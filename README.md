# Ritaj Smart Resto - POS Hybride

Système de Point de Vente (POS) pour restaurants basé sur une architecture Hybride (Local + Cloud) utilisant **Laravel 11** et **FilamentPHP v3**.

## 📄 Fiche Technique

### 1. Architecture Système & Déploiement

Le système fonctionne selon un modèle **Hybride Maître/Esclave** :

*   **Instance A (Local - Restaurant)** : Installée sur un mini-PC/Serveur local (ex: Windows avec Laragon ou Linux). Elle gère le POS, les serveurs (Wifi local), l'imprimante et la cuisine. **Elle ne dépend pas d'internet pour vendre.**
*   **Instance B (Cloud - Manager)** : Hébergée sur un VPS (DigitalOcean/Hetzner). C'est un tableau de bord "Miroir" pour le patron. Elle reçoit les données de l'Instance A via API.

#### Flux de données
*   **Serveurs (Mobile)** : Se connectent via Wifi à l'IP locale du serveur (ex: `192.168.1.50`).
*   **Imprimante** : Reliée en USB/Ethernet au serveur local.
*   **Agent de Sync** : Une tâche planifiée (Laravel Scheduler) sur le serveur local envoie les données (JSON) vers l'API du Cloud toutes les 5 minutes.

---

### 2. Stack Technique Détaillée

*   **Backend Framework** : Laravel 11.x
*   **Admin Panel & UI Components** : FilamentPHP v3.
*   **Frontend Caisse & Serveurs** : Filament Custom Pages + Livewire (Full Page Components).
*   **Base de Données** : MySQL 8.0.
*   **Temps Réel (Local)** : Laravel Reverb (WebSocket server inclus dans Laravel 11) pour communication Serveur ↔ Cuisine.
*   **Impression** : Package `mike42/escpos-php` (Driver direct RAW) ou `rawbt` (Android protocol si tablettes Android).

---

### 3. Modélisation de la Donnée (Points Critiques)

Utilisation d'**UUIDs** comme clés primaires pour éviter les conflits de synchronisation.

#### Table : `orders`
*   `uuid` (Primary Key, char 36)
*   `local_id` (Auto-increment, pour affichage ticket #102)
*   `table_id` (Relation)
*   `user_id` (User)
*   `status` (enum: pending, sent_to_kitchen, ready, paid, cancelled)
*   `sync_status` (boolean: false = à envoyer au cloud, true = synchronisé)
*   `payment_method` (cash, card)
*   `total_amount` (decimal)

#### Table : `tables`
*   `id`
*   `name` (Ex: "Terrasse 1")
*   `qr_code_hash` (Pour futur scan client)
*   `current_order_uuid` (Lien vers la commande active)

---

### 4. Modules Fonctionnels & UX

#### A. Interface Serveur (Mobile View - Responsive)
*   **Cible** : Smartphone via navigateur mobile.
*   **Techno** : Filament Custom Page avec layout "Pleine largeur" et CSS spécifique mobile.
*   **UX/UI** :
    *   **Login Rapide** : Code PIN à 4 chiffres.
    *   **Plan de Salle** : Grille simple (Vert = Libre, Rouge = Occupé).
    *   **Prise de commande** : Gros boutons catégories.
    *   **Actions** : Bouton flottant "Envoyer en Cuisine".

#### B. Interface Caisse (Desktop POS)
*   **Cible** : Écran tactile ou PC Souris au comptoir.
*   **Fonctionnalités** :
    *   Vue globale des tables.
    *   Fusion/Transfert de tables.
    *   Checkout (Split bill, Remises).
    *   Fermeture de Caisse (Z-Ticket).

#### C. L'Agent de Synchronisation
*   **API Endpoint (Cloud)** : Route sécurisée (Sanctum) `POST /api/sync/orders`.
*   **Scheduler (Local)** : Commande `app/Console/Commands/SyncOrdersToCloud.php` exécutée chaque minute.

---

### 5. Spécifications Techniques

#### Configuration Filament Mobile
*   Masquer la sidebar (`->sidebarCollapsibleOnDesktop()`).
*   Utiliser **Filament PWA Plugin**.
*   Désactiver la recherche globale.

#### Impression Thermique
*   Service `PrinterService`.
*   **Local** : `Mike42\Escpos` vers IP imprimante.
*   **Logique** : Header -> Body -> Footer. Flag `printed` pour les items cuisine.

#### Dashboard Patron (Cloud)
*   Filament Widgets : CA Temps réel, Top ventes, Performance serveurs.
*   Stock en lecture seule sur le Cloud.
