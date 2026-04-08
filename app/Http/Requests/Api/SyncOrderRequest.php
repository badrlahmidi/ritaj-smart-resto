<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SyncOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid' => ['required', 'uuid'],
            'local_id' => ['nullable', 'integer', 'min:1'],
            'order' => ['required', 'array'],
            'order.local_id' => ['nullable', 'integer', 'min:1'],
            'order.status' => ['required', 'string'],
            'order.total_amount' => ['required', 'numeric', 'min:0'],
            'order.payment_status' => ['nullable', 'string'],
            'order.payment_method' => ['nullable', 'string'],
            'order.type' => ['nullable', 'string'],
            'order.created_at' => ['nullable', 'date'],
            'order.updated_at' => ['nullable', 'date'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['nullable', 'integer', 'min:1'],
            'items.*.product_name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.subtotal' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
            'table' => ['nullable', 'array'],
            'table.name' => ['nullable', 'string', 'max:255'],
            'table.qr_code_hash' => ['nullable', 'string', 'max:255'],
            'server' => ['nullable', 'array'],
            'server.name' => ['nullable', 'string', 'max:255'],
            'server.email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
