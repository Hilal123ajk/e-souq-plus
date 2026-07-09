<?php

declare(strict_types=1);

namespace App\Http\Requests\Store\Concerns;

trait ValidatesCheckoutOrder
{
    /**
     * @return array<string, mixed>
     */
    protected function checkoutOrderRules(): array
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.variant_image_id' => ['nullable', 'integer'],
            'items.*.variant_label' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareCheckoutOrderValidation(): void
    {
        $this->merge([
            'first_name' => $this->sanitizeCheckoutInput($this->input('first_name')),
            'last_name' => $this->sanitizeCheckoutInput($this->input('last_name')),
            'email' => $this->sanitizeCheckoutInput($this->input('email')),
            'phone' => preg_replace('/\D+/', '', (string) $this->input('phone')),
            'address' => $this->sanitizeCheckoutInput($this->input('address')),
            'city' => $this->sanitizeCheckoutInput($this->input('city')),
            'country' => $this->sanitizeCheckoutInput($this->input('country')),
            'notes' => $this->sanitizeCheckoutInput($this->input('notes')),
        ]);
    }

    private function sanitizeCheckoutInput(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return trim(str_replace(['<', '>'], '', $value));
    }
}
