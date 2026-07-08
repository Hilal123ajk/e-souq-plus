<?php

declare(strict_types=1);

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:100'],
            'last_name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\d{9,15}$/'],
            'address' => ['required', 'string', 'min:5', 'max:500'],
            'city' => ['required', 'string', 'min:2', 'max:100'],
            'country' => ['required', 'string', 'min:2', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', Rule::in(['cod'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.variant_image_id' => ['nullable', 'integer'],
            'items.*.variant_label' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => $this->sanitize($this->input('first_name')),
            'last_name' => $this->sanitize($this->input('last_name')),
            'email' => $this->sanitize($this->input('email')),
            'phone' => preg_replace('/\D+/', '', (string) $this->input('phone')),
            'address' => $this->sanitize($this->input('address')),
            'city' => $this->sanitize($this->input('city')),
            'country' => $this->sanitize($this->input('country')),
            'notes' => $this->sanitize($this->input('notes')),
        ]);
    }

    private function sanitize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return trim(str_replace(['<', '>'], '', $value));
    }
}
