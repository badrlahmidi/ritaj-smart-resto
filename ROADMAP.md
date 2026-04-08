# 🚀 ROADMAP — Ritaj Smart Resto v3.0 (Production-Ready)

> **Objectif** : Passer de 6.2/10 → 10/10 — Solution prête pour la production et la commercialisation.  
> **Auteur** : Expert Engineer — 10+ ans d'expérience POS/Restaurant Tech  
> **Date** : Avril 2026  
> **Méthode** : Incrémentale par Sprint — Chaque phase livre une valeur mesurable.

---

## 📋 Table des matières

1. [Vue d'ensemble de l'état actuel](#1-vue-densemble-de-létat-actuel)
2. [Architecture cible 10/10](#2-architecture-cible-1010)
3. [PHASE 1 — Corrections critiques & Stabilisation](#phase-1--corrections-critiques--stabilisation-sprint-1)
4. [PHASE 2 — Offline-First & PWA](#phase-2--offline-first--pwa-sprint-2)
5. [PHASE 3 — Impression thermique industrielle](#phase-3--impression-thermique-industrielle-sprint-3)
6. [PHASE 4 — Synchronisation Cloud complète](#phase-4--synchronisation-cloud-complète-sprint-4)
7. [PHASE 5 — Temps réel (WebSocket/Reverb)](#phase-5--temps-réel-websocketreverb-sprint-5)
8. [PHASE 6 — Caisse & Finance complètes](#phase-6--caisse--finance-complètes-sprint-6)
9. [PHASE 7 — UX/UI Production-Grade](#phase-7--uxui-production-grade-sprint-7)
10. [PHASE 8 — Sécurité, Tests & DevOps](#phase-8--sécurité-tests--devops-sprint-8)
11. [PHASE 9 — Fonctionnalités commerciales avancées](#phase-9--fonctionnalités-commerciales-avancées-sprint-9)
12. [PHASE 10 — Packaging & Déploiement commercial](#phase-10--packaging--déploiement-commercial-sprint-10)
13. [Matrice de progression](#matrice-de-progression)

---

## 1. Vue d'ensemble de l'état actuel

| Module | État actuel | Note /10 | Objectif |
|--------|------------|----------|----------|
| Gestion restaurant (menus, tables) | ✅ Fonctionnel | 8 | 10 |
| Offline First | ⚠️ Partiel (DB locale, pas PWA) | 3 | 10 |
| Prise commande serveurs mobile | ✅ Livewire responsive | 6 | 10 |
| Serveur local sur caisse principale | ✅ Architecture prévue | 7 | 10 |
| Accès à distance patron (Cloud) | ⚠️ Sync stub vide | 4 | 10 |
| Multi-imprimantes thermiques LAN | ✅ Station routing | 7 | 10 |
| Gestion stock / ingrédients | ✅ Avec recettes | 7 | 10 |
| Paiement / Caisse | ⚠️ printBill cassé, Z-ticket absent | 5 | 10 |
| Sécurité & Tests | ⚠️ Tests quasi absents | 3 | 10 |
| UI/UX Mobile & Touch | ⚠️ Basique | 5 | 10 |

### Bugs critiques identifiés

| # | Bug | Fichier | Ligne | Impact |
|---|-----|---------|-------|--------|
| BUG-1 | `printBill()` appelle `$this->getConnector()` sans argument `Printer $printerModel` | `app/Services/PrinterService.php` | L151 | **Fatal Error** — impression caisse cassée |
| BUG-2 | API Cloud `POST /api/sync/orders` retourne `synced` sans persister | `routes/api.php` | L16-19 | **Sync non fonctionnelle** côté Cloud |
| BUG-3 | `CashRegisterLogic::processPayment()` appelle `printBill()` cassé | `app/Livewire/CashRegisterLogic.php` | L73 | Crash caisse legacy |
| BUG-4 | `verifyPin()` charge tous admins en mémoire — non scalable | `app/Livewire/Pos/ProPos.php` | L341-343 | Performance + timing attack |
| BUG-5 | Route `/pos` définie 2 fois dans `web.php` (L19 et L30) | `routes/web.php` | L19,30 | Route conflictuelle |

---

## 2. Architecture cible 10/10

```
┌──────────────────────────────────────────────────────────┐
│                   CLOUD (VPS Hetzner/DO)                  │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────────┐  │
│  │  Dashboard    │  │  API Sync    │  │  Analytics    │  │
│  │  Patron (RO)  │  │  Receiver    │  │  (Widgets)    │  │
│  └──────────────┘  └──────────────┘  └───────────────┘  │
│            ↑ HTTPS + Sanctum Token (push up)             │
│            │ (+ bidirectionnel: prix/menus push down)    │
└────────────┼─────────────────────────────────────────────┘
             │
    ═══════ INTERNET (optionnel — offline-first) ═══════
             │
┌────────────┼─────────────────────────────────────────────┐
│  LOCAL RESTAURANT (Serveur sur Caisse Principale)        │
│  ┌─────────┴───────┐                                     │
│  │  Laravel + MySQL │ ← SQLite fallback si MySQL down    │
│  │  + Reverb WS     │                                    │
│  └──┬──────┬──────┬─┘                                    │
│     │      │      │                                      │
│  ┌──┴──┐ ┌─┴───┐ ┌┴────────┐                            │
│  │ POS │ │ KDS │ │ Serveurs│  ← Smartphones via WiFi    │
│  │Caisse│ │Écran│ │ Mobile  │    PWA (offline capable)   │
│  └──┬──┘ └─────┘ └─────────┘                            │
│     │                                                    │
│  ┌──┴──────────────────────────────┐                    │
│  │  IMPRIMANTES THERMIQUES (LAN)   │                    │
│  │  🖨️ Caisse (192.168.1.100)      │                    │
│  │  🖨️ Cuisine (192.168.1.101)     │                    │
│  │  🖨️ Bar (192.168.1.102)         │                    │
│  │  🖨️ Pizzeria (192.168.1.103)    │                    │
│  └─────────────────────────────────┘                    │
└──────────────────────────────────────────────────────────┘
```

---

## PHASE 1 — Corrections critiques & Stabilisation (Sprint 1)

> **Objectif** : Zéro crash, base stable pour construire.

### 1.1 Fix `PrinterService::printBill()` — BUG-1

**Fichier** : `app/Services/PrinterService.php`

**Action** : Réécrire `printBill()` pour :
- Accepter un `Order $order` en paramètre
- Récupérer l'imprimante caisse (tag `cashier`) ou première imprimante active
- Appeler `$this->getConnector($printerModel)` avec le bon argument
- Implémenter le layout ticket complet (header, items, totaux, TVA, footer, QR code optionnel)
- Ajouter cash drawer pulse `$printer->pulse()`
- Déléguer le layout au `ReceiptPrinterService` existant pour éviter la duplication

### 1.2 Fix `CashRegisterLogic::processPayment()` — BUG-3

**Fichier** : `app/Livewire/CashRegisterLogic.php`

**Action** : Remplacer l'appel `printBill()` cassé par un appel au `ReceiptPrinterService::printOrder()` qui fonctionne.

### 1.3 Fix route dupliquée `/pos` — BUG-5

**Fichier** : `routes/web.php`

**Action** : 
- Supprimer la ligne 19 (doublon) ou la ligne 30
- Garder une seule route `/pos` pointant vers le bon composant (`Terminal` ou `PosPage`)
- Clarifier la stratégie : `Terminal` = caisse rapide, `ProPos` via `PosPage` = serveur complet

### 1.4 Fix `verifyPin()` — BUG-4

**Fichier** : `app/Livewire/Pos/ProPos.php`

**Action** :
- Remplacer le chargement de tous les admins par une requête optimisée
- Stocker le hash PIN dans `users` directement (ou garder `user_pins` mais faire un `JOIN`)
- Utiliser `hash_equals()` pour comparaison constant-time du PIN hashé
- Limiter les tentatives (rate limiting) : 3 essais puis blocage 30 secondes

### 1.5 Ajouter Factories manquantes

**Action** : Créer des factories pour les modèles critiques qui n'en ont pas :
- `OrderFactory` (existe dans les tests mais pas physiquement ?)
- `ProductFactory`
- `TableFactory`
- `CategoryFactory`
- `PrinterFactory`

### 1.6 Corriger le DatabaseSeeder

**Fichier** : `database/seeders/DatabaseSeeder.php`

**Action** : Créer un seeder complet avec :
- 1 admin, 2 serveurs, 1 manager avec PINs
- 5 catégories (Entrées, Plats, Boissons, Desserts, Pizzas)
- 20+ produits avec prix, images placeholder, kitchen_station
- 3 zones (Salle, Terrasse, VIP) avec tables
- 3 imprimantes (Caisse, Cuisine, Bar) avec station_tags
- 2 fournisseurs + ingrédients de base

---

## PHASE 2 — Offline-First & PWA (Sprint 2)

> **Objectif** : L'application fonctionne à 100% sans internet, les serveurs prennent les commandes via PWA mobile.

### 2.1 PWA Manifest & Service Worker

**Actions** :
- Ajouter `public/manifest.json` avec icônes, `display: standalone`, `orientation: portrait`
- Créer le Service Worker (`public/sw.js`) avec stratégie **Network-First pour API**, **Cache-First pour assets**
- Cacher les assets Vite (CSS/JS), les images produits, et les polices
- Ajouter le meta tag `<meta name="theme-color">` dans les layouts
- Enregistrer le SW dans `resources/js/app.js`

### 2.2 Configuration PWA Laravel

**Actions** :
- Ajouter les headers `Cache-Control` appropriés dans les middlewares
- Configurer Vite pour générer un fichier de précache (`workbox-precaching`)
- Ajouter `<link rel="manifest">` dans tous les layouts (pos-app, kds, pos)

### 2.3 Résilience réseau Livewire

**Actions** :
- Ajouter le hook Livewire `wire:offline` pour afficher un indicateur offline
- Implémenter un mécanisme de queue locale via `localStorage` pour les actions critiques (sendToKitchen) si la connexion au serveur local est perdue temporairement
- Ajouter reconnexion automatique avec `wire:poll` ou `Echo` reconnect

### 2.4 Optimisations Mobile

**Actions** :
- Ajouter `<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">`
- Désactiver le zoom iOS Safari avec `touch-action: manipulation`
- Ajouter les icônes Apple Touch et splash screens
- Tester sur Chrome Android, Safari iOS, Samsung Internet

---

## PHASE 3 — Impression thermique industrielle (Sprint 3)

> **Objectif** : Multi-imprimantes LAN fiables, retry automatique, file d'attente.

### 3.1 Refactoring complet du service d'impression

**Actions** :
- Créer `App\Services\Printing\PrintManager` comme point d'entrée unique
- Implémenter le pattern **Strategy** pour les connecteurs (Network, USB, Windows, Dummy)
- Ajouter un **Print Queue** via Laravel Jobs pour ne jamais bloquer le POS
- Créer une table `print_jobs` avec statut (pending, printing, done, failed, retrying)

### 3.2 `PrintManager` — Architecture

```
App\Services\Printing\
├── PrintManager.php          ← Façade principale
├── Connectors/
│   ├── ConnectorInterface.php
│   ├── NetworkConnector.php   ← ESC/POS via IP:9100
│   ├── WindowsConnector.php   ← Share Windows
│   └── DummyConnector.php     ← Test/Dev
├── Templates/
│   ├── TemplateInterface.php
│   ├── KitchenTicketTemplate.php
│   ├── ReceiptTemplate.php
│   ├── ZTicketTemplate.php
│   └── ProformaTemplate.php
├── Jobs/
│   └── ProcessPrintJob.php    ← Laravel Queue Job
└── ReceiptPrinterService.php  ← (existant, migrer vers PrintManager)
```

### 3.3 Gestion multi-imprimantes améliorée

**Actions** :
- Ajouter au model `Printer` : `paper_width` (58mm, 80mm), `auto_cut`, `cash_drawer`, `max_retries`
- Créer un health-check ping pour chaque imprimante (tentative socket TCP)
- Dashboard Filament : statut des imprimantes en temps réel (vert/rouge)
- Retry automatique : 3 tentatives avec backoff exponentiel avant marquage `failed`

### 3.4 Templates d'impression enrichis

**Actions** :
- **Ticket Cuisine** : Gros texte quantité + nom, options en dessous, notes en inverse vidéo, numéro ticket visible
- **Ticket Caisse/Reçu** : Logo (si imprimante 80mm), header resto, items avec prix, sous-total, remise, TVA, total, footer WiFi, QR code paiement optionnel
- **Z-Ticket** : Total ventes jour, nombre de tickets, répartition par mode de paiement, annulations, serveur le plus performant
- **Proforma** : Pré-addition avant paiement

### 3.5 Impression par événement

**Actions** :
- `sendToKitchen()` → dispatch `PrintKitchenTicket` Job (async, ne bloque pas le POS)
- `processPayment()` → dispatch `PrintReceipt` Job
- `closeRegister()` → dispatch `PrintZTicket` Job
- Chaque Job log le résultat dans `print_jobs` table

---

## PHASE 4 — Synchronisation Cloud complète (Sprint 4)

> **Objectif** : Le patron voit tout à distance, peut modifier menus/prix depuis le cloud.

### 4.1 API Cloud — Persistance réelle

**Fichier** : `routes/api.php`

**Actions** :
- Implémenter `App\Http\Controllers\Api\SyncController`
- `POST /api/sync/orders` : Valider avec FormRequest, upsert les commandes + items
- `POST /api/sync/products` : Recevoir les produits du local
- `POST /api/sync/stock-movements` : Recevoir les mouvements de stock
- `GET /api/sync/menu-updates` : Le local pull les changements de menu faits par le patron
- `GET /api/sync/settings` : Pull les settings mis à jour

### 4.2 Sync bidirectionnelle

**Actions** :
- **Local → Cloud** (existant, à compléter) : Commandes payées, mouvements de stock, statistiques serveurs
- **Cloud → Local** (nouveau) : Modifications de prix, ajout/suppression produits, changement de catégories, activation/désactivation de modules
- Ajouter `updated_at` comparison pour résolution de conflits (Last Writer Wins)
- Ajouter une table `sync_log` pour traçabilité

### 4.3 Commande `SyncOrdersToCloud` améliorée

**Actions** :
- Sync aussi les `payments`, `stock_movements` (pas seulement les orders)
- Ajouter la sync descendante (Cloud → Local) en second temps
- Ajouter un mode `--dry-run` pour vérifier sans envoyer
- Ajouter métriques Prometheus-compatible pour monitoring

### 4.4 Dashboard Patron (Cloud)

**Actions** :
- Widgets Filament :
  - CA temps réel (SSE ou polling 30s)
  - Commandes actives live
  - Top 10 produits vendus (période configurable)
  - Performance serveurs (CA par serveur, nombre de couverts)
  - Alertes stock bas
- Page de gestion des prix à distance (avec sync push vers local)
- Historique des synchronisations avec statuts

---

## PHASE 5 — Temps réel (WebSocket/Reverb) (Sprint 5)

> **Objectif** : KDS, serveurs et caisse communiquent en temps réel sans polling.

### 5.1 Installation & Configuration Laravel Reverb

**Actions** :
- Ajouter `laravel/reverb` au projet
- Configurer dans `.env` : `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_HOST=0.0.0.0`, `REVERB_PORT=8080`
- Configurer `config/broadcasting.php` pour driver `reverb`
- Changer `BROADCAST_CONNECTION=reverb` dans `.env`
- Ajouter `laravel-echo` et `pusher-js` côté client

### 5.2 Événements broadcast

**Actions** :
- Créer `App\Events\OrderSentToKitchen` → broadcast sur channel `kitchen`
- Créer `App\Events\OrderItemReady` → broadcast sur channel `pos.{userId}`
- Créer `App\Events\OrderPaid` → broadcast sur channel `kitchen` (retirer de l'écran)
- Créer `App\Events\OrderCancelled` → broadcast sur channel `kitchen`
- Créer `App\Events\TableStatusChanged` → broadcast sur channel `floor-plan`

### 5.3 Intégration KDS temps réel

**Fichier** : `app/Livewire/Kds/KdsBoard.php`

**Actions** :
- Remplacer le `getListeners` statique par Echo dynamique
- Le KDS reçoit `OrderSentToKitchen` et ajoute la commande à l'écran sans refresh
- Le KDS envoie `OrderItemReady` quand le cuisinier tap "Prêt"
- Ajouter un timer visible par commande (temps d'attente, couleur rouge après 15 min)
- Son d'alerte à la réception d'une nouvelle commande

### 5.4 Intégration POS temps réel

**Actions** :
- Le serveur reçoit `OrderItemReady` → notification toast "Table 5 : Pizza prête !"
- Le plan de salle se met à jour en live quand une table change de statut
- Alerte sonore quand une commande est prête à servir

---

## PHASE 6 — Caisse & Finance complètes (Sprint 6)

> **Objectif** : Z-Ticket, split bill fonctionnel, rapports financiers complets.

### 6.1 Z-Ticket (Fermeture de caisse)

**Actions** :
- Créer `App\Services\CashRegister\CashRegisterService`
- Méthode `openRegister($userId, $openingFloat)` : Enregistrer l'ouverture avec montant initial
- Méthode `closeRegister($userId)` : Calculer le Z-Ticket
- Créer la migration `create_cash_register_sessions_table` :
  - `id`, `user_id`, `opened_at`, `closed_at`, `opening_float`, `closing_amount`
  - `expected_amount`, `difference`, `total_sales`, `total_orders`, `cancellations_count`
  - `payment_breakdown` (JSON: cash, card, etc.)

### 6.2 Z-Ticket : Données calculées

- Total ventes HT et TTC
- Nombre de tickets / commandes
- Répartition par mode de paiement (espèces, CB, etc.)
- Répartition par type de commande (sur place, emporter, livraison)
- Montant des remises accordées
- Nombre et montant des annulations
- Performance par serveur
- Écart de caisse (attendu vs réel)
- Impression automatique via `PrintManager`

### 6.3 Split Bill complet

**Fichier** : `app/Livewire/Pos/ProPos.php`

**Actions** :
- Implémenter `splitByCount($count)` : Diviser le total en N parts égales
- Implémenter `splitByItems($selectedIndexes)` : Créer un sous-paiement pour les items sélectionnés
- Implémenter `splitCustomAmount($amounts)` : Montants personnalisés
- Chaque split crée un enregistrement `Payment` séparé
- Tracker le `remaining_amount` sur la commande
- La commande passe en `Paid` seulement quand `remaining_amount = 0`

### 6.4 Rapports financiers avancés

**Actions** :
- Enrichir `FinancialReport` :
  - Ajout Marge brute (CA - Coût matière) si cost renseigné
  - CA par catégorie de produit
  - Graphique évolution hebdomadaire
  - Export Excel via `pxlrbt/filament-excel` (déjà installé)
- Créer un rapport `StockValuationReport` : Valeur du stock actuel
- Créer un rapport `WasteReport` : Produits gaspillés (annulations post-cuisine)

---

## PHASE 7 — UX/UI Production-Grade (Sprint 7)

> **Objectif** : Interface professionnelle, rapide, tactile, sonore — adaptée au stress du service.

### 7.1 POS Interface — Refonte UX

**Actions** :
- **Plan de salle visuel** : Composant drag-and-drop pour positionner les tables (utiliser les `position_x`, `position_y` déjà en DB)
- **Couleurs de statut** : Vert (libre), Rouge (occupé), Orange (addition demandée), Gris (réservé)
- **Animations** : Transition Livewire fluides entre les vues (tables → ordering → payment)
- **Sons** : Bip à l'ajout produit (existe), alerte nouvelle commande prête, erreur

### 7.2 Interface Serveur Mobile — Optimisation

**Actions** :
- Layout 100% mobile first — pas de sidebar, bottom navigation
- Gros boutons tactiles (min 48px touch target selon Material Design)
- Swipe gauche pour supprimer un article du panier
- Pull-to-refresh sur le plan de salle
- Mode sombre pour les services du soir
- Haptic feedback (vibration) sur les actions critiques

### 7.3 KDS — Affichage cuisine

**Actions** :
- Layout en colonnes style Kanban (Nouvelle | En cours | Prête)
- Timer couleur : Vert < 5min, Orange < 10min, Rouge > 10min, Clignotant > 15min
- Gros texte (lisible à 2m de l'écran)
- Bouton "Bump" plein écran pour marquer une commande prête
- Regroupement par station (si multi-station)
- Son différent par type de commande (sur place vs emporter)

### 7.4 Thème & Branding

**Actions** :
- Palette de couleurs configurable dans les Settings (GeneralSettings)
- Logo dynamique sur tous les écrans depuis les settings
- Mode sombre (dark mode) toggle
- Polices optimisées pour écran tactile (Inter, system-ui)

---

## PHASE 8 — Sécurité, Tests & DevOps (Sprint 8)

> **Objectif** : Application robuste, testée, sécurisée, deployable automatiquement.

### 8.1 Tests PHPUnit / Pest

**Actions** :
- **Unit Tests** :
  - `OrderServiceTest` : Vérifier la déduction de stock
  - `PrinterServiceTest` : Vérifier les connecteurs (mock)
  - `CashRegisterServiceTest` : Vérifier le calcul du Z-Ticket
  - `ProductPricingTest` : Vérifier `getPriceByType()` pour chaque type
- **Feature Tests** :
  - `OrderSyncTest` (existe, enrichir) : Ajouter cas edge (timeout, données corrompues)
  - `PosWorkflowTest` : Créer commande → envoyer cuisine → préparer → payer → vérifier stock
  - `PaymentFlowTest` : Full, Split Count, Split Items
  - `PrintingTest` : Vérifier dispatch des jobs, template rendering
  - `AuthPinTest` : Vérifier PIN correct, PIN incorrect, rate limit
- **Browser Tests** (optionnel, Dusk) :
  - Parcours complet serveur mobile
  - Checkout caisse
- **Objectif** : Couverture > 70% sur les services critiques

### 8.2 Sécurité

**Actions** :
- **Rate Limiting** : Ajouter `throttle:5,1` sur `verifyPin()` (5 essais/minute)
- **CSRF** : Vérifier que toutes les routes web ont le middleware `@csrf`
- **Sanctum Scoping** : Ajouter des abilities/scopes sur les tokens (ex: `sync:write`, `menu:read`)
- **Input Validation** : Ajouter des `FormRequest` pour toutes les routes API
- **SQL Injection** : Auditer les requêtes `whereRaw` / `selectRaw` (FinancialReport)
- **XSS** : Vérifier que toutes les Blade views utilisent `{{ }}` et non `{!! !!}` sauf cas justifié
- **Logs sensibles** : Ne pas logger de données personnelles (client phone, address) en production
- **Hashing PIN** : Confirmer que `Hash::check()` est constant-time (c'est le cas avec bcrypt)

### 8.3 Internationalisation (i18n)

**Actions** :
- Remplacer tous les textes hardcodés français par des clés `__('messages.xxx')`
- Créer `lang/fr/messages.php` et `lang/ar/messages.php` (français + arabe pour le Maroc)
- Les labels Filament utilisent déjà le système de traduction → configurer pour FR
- Ajouter un sélecteur de langue dans les Settings

### 8.4 DevOps & CI/CD

**Actions** :
- Créer `.github/workflows/ci.yml` :
  - `laravel-pint` (lint) → `phpunit` (tests) → build Vite
- Créer un script d'installation `install.sh` / `install.bat` :
  - `composer install` → `npm install && npm run build` → `php artisan migrate --seed` → `php artisan key:generate`
- Docker Compose pour le dev local (MySQL + PHP + Reverb + Redis)
- Documentation déploiement production (Laragon Windows / Docker Linux)

---

## PHASE 9 — Fonctionnalités commerciales avancées (Sprint 9)

> **Objectif** : Fonctionnalités différenciantes pour la vente du produit.

### 9.1 QR Code Client (Self-Order)

**Actions** :
- Générer un QR Code unique par table (utiliser `qr_code_hash` déjà en DB)
- Page publique `/menu/{hash}` : Menu digital consultable sans login
- Phase 2 : Le client peut commander depuis son téléphone (ajout au panier → validation serveur)
- Pas de paiement en ligne dans V1 (juste consultation + appel serveur)

### 9.2 Programme de fidélité (basique)

**Actions** :
- Ajouter `loyalty_points` sur le model `Order`
- Règle : 1 DH dépensé = 1 point
- Seuil configurable pour remise (ex: 500 points = 10% remise)
- Recherche client par téléphone à la caisse

### 9.3 Réservations

**Actions** :
- Créer model `Reservation` : date, heure, nombre de couverts, nom, téléphone, table_id, statut
- Page Filament de gestion des réservations
- Statut table "réservé" visible sur le plan de salle
- Notification rappel (optionnel, via SMS ou WhatsApp API)

### 9.4 Multi-restaurant (SaaS-Ready)

**Actions** :
- Ajouter un `tenant_id` global (préparer sans activer)
- Chaque restaurant = une instance locale indépendante
- Le Cloud peut gérer N restaurants avec un dashboard multi-tenant
- Cette phase est un **pré-requis architectural** pour la commercialisation à grande échelle

---

## PHASE 10 — Packaging & Déploiement commercial (Sprint 10)

> **Objectif** : Le produit peut être vendu, installé et maintenu chez un client en < 1 heure.

### 10.1 Installeur Windows (Caisse locale)

**Actions** :
- Script PowerShell/Batch `install-ritaj.bat` :
  1. Vérifier les pré-requis (PHP 8.2, MySQL, Node)
  2. Ou installer Laragon portable automatiquement
  3. Cloner le repo / extraire le zip
  4. `composer install --no-dev`
  5. `npm run build`
  6. `php artisan migrate --seed`
  7. Créer le raccourci bureau
  8. Configurer le Windows Task Scheduler pour `php artisan schedule:run`
  9. Configurer le démarrage automatique de Reverb
- Guide PDF de 5 pages avec captures d'écran

### 10.2 Installeur Cloud (Patron)

**Actions** :
- Script Ansible/Bash pour VPS :
  1. Installer Nginx + PHP-FPM + MySQL + Certbot
  2. Déployer le code
  3. Configurer SSL Let's Encrypt
  4. Configurer le cron scheduler
  5. Créer le premier compte admin
- Alternative : Docker Compose one-click

### 10.3 Documentation utilisateur

**Actions** :
- Guide Patron (PDF/Web) : Dashboard, rapports, gestion des prix à distance
- Guide Caissier : Ouverture/Fermeture caisse, paiement, Z-Ticket
- Guide Serveur : Prise de commande mobile, ajout notes, demander l'addition
- Guide Technique : Architecture, déploiement, configuration imprimantes, troubleshooting
- Vidéos tutoriels courtes (2-3 min chacune)

### 10.4 Monitoring & Support

**Actions** :
- Endpoint `/health` pour monitoring (DB, imprimantes, sync status)
- Alertes email si sync échoue > 1 heure
- Log rotation configuré
- Backup automatique quotidien via `spatie/laravel-backup` (déjà installé, à configurer)

---

## Matrice de progression

| Phase | Module amélioré | Note avant | Note après | Priorité |
|-------|----------------|-----------|-----------|----------|
| 1 | Stabilisation & Bugfix | 5.0 | 7.0 | 🔴 CRITIQUE |
| 2 | Offline-First & PWA | 3.0 | 8.5 | 🔴 CRITIQUE |
| 3 | Impression thermique | 7.0 | 9.5 | 🔴 CRITIQUE |
| 4 | Sync Cloud complète | 4.0 | 9.0 | 🟡 HAUTE |
| 5 | Temps réel (Reverb) | 2.0 | 9.0 | 🟡 HAUTE |
| 6 | Caisse & Finance | 5.0 | 9.5 | 🟡 HAUTE |
| 7 | UX/UI Production | 5.0 | 9.0 | 🟡 HAUTE |
| 8 | Sécurité & Tests | 3.0 | 9.0 | 🟡 HAUTE |
| 9 | Fonctionnalités avancées | 0.0 | 8.0 | 🟢 MOYENNE |
| 10 | Packaging commercial | 0.0 | 9.0 | 🟢 MOYENNE |

### Score final projeté : **9.2/10** (après Phase 8) → **10/10** (après Phase 10)

---

## 📦 Dépendances à ajouter

| Package | Usage | Phase |
|---------|-------|-------|
| `laravel/reverb` | WebSocket temps réel (KDS, notifications) | 5 |
| `pusher/pusher-php-server` | Dependency de Reverb | 5 |
| `simplesoftwareio/simple-qrcode` | QR codes tables + tickets | 9 |
| `barryvdh/laravel-dompdf` | Export PDF rapports | 6 |
| `workbox-cli` (npm) | Service Worker PWA | 2 |

---

## ⏭️ Prochaine action

> **En attente de votre confirmation pour commencer l'implémentation.**
>
> Je recommande de démarrer par **PHASE 1** (corrections critiques) car elle prend < 2h et stabilise immédiatement le système, puis enchaîner avec **PHASE 2** (Offline-First) et **PHASE 3** (Impression) car ce sont les 3 objectifs principaux du patron.
>
> 👉 **Confirmez et je lance l'exécution phase par phase.**
