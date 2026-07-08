<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends FormRequest
{
    use ValidatesProductUploads;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return array_merge([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'description' => ['nullable', 'string'],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'main_category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('parent_id')],
            'sub_category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->whereNotNull('parent_id')],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'image' => $this->imageMimeRules(required: false),
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'has_variants' => ['sometimes', 'boolean'],
        ], $this->galleryRules());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'has_variants' => $this->boolean('has_variants'),
            'brand_id' => $this->input('brand_id') ?: null,
            'sub_category_id' => $this->input('sub_category_id') ?: null,
            'category_id' => $this->input('sub_category_id') ?: $this->input('main_category_id'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $subId = $this->input('sub_category_id');
            $mainId = $this->input('main_category_id');

            if (! $subId || ! $mainId) {
                return;
            }

            $sub = Category::query()->find($subId);

            if ($sub === null || (int) $sub->parent_id !== (int) $mainId) {
                $validator->errors()->add('sub_category_id', 'The selected sub category does not belong to the main category.');
            }
        });
    }

    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if ($key !== null) {
            return $data;
        }

        unset($data['main_category_id'], $data['sub_category_id']);

        return $data;
    }
}
