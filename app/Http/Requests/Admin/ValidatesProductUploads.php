<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

trait ValidatesProductUploads
{
    protected function imageMimeRules(bool $required = true): array
    {
        $rules = ['file', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }

    protected function galleryRules(): array
    {
        return [
            'gallery_images' => ['nullable', 'array', 'max:8'],
            'gallery_images.*' => ['file', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
            'gallery_labels' => ['nullable', 'array'],
            'gallery_labels.*' => ['nullable', 'string', 'max:100'],
            'remove_gallery_ids' => ['nullable', 'array'],
            'remove_gallery_ids.*' => ['integer'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateUploadField($validator, 'image', 'Hero image');

            if (is_array($this->file('gallery_images'))) {
                foreach ($this->file('gallery_images') as $index => $file) {
                    if ($file instanceof UploadedFile && ! $file->isValid()) {
                        $label = $this->boolean('has_variants') ? 'Color image' : 'Gallery image';
                        $validator->errors()->add(
                            "gallery_images.{$index}",
                            $this->uploadErrorMessage($file, $label)
                        );
                    }
                }
            }

            if ($this->boolean('has_variants') && is_array($this->file('gallery_images'))) {
                $labels = $this->input('gallery_labels', []);

                foreach ($this->file('gallery_images') as $index => $file) {
                    if (! $file instanceof UploadedFile || ! $file->isValid()) {
                        continue;
                    }

                    $label = is_array($labels) ? trim((string) ($labels[$index] ?? '')) : '';

                    if ($label === '') {
                        $validator->errors()->add(
                            "gallery_labels.{$index}",
                            'A color name is required for each variant image.'
                        );
                    }
                }
            }
        });
    }

    protected function validateUploadField(Validator $validator, string $field, string $label): void
    {
        $file = $this->file($field);

        if (! $file instanceof UploadedFile) {
            return;
        }

        if (! $file->isValid()) {
            $validator->errors()->add($field, $this->uploadErrorMessage($file, $label));
        }
    }

    protected function uploadErrorMessage(UploadedFile $file, string $label): string
    {
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "{$label} is too large. PHP allows up to ".ini_get('upload_max_filesize').' per file.',
            UPLOAD_ERR_PARTIAL => "{$label} upload was interrupted. Please try again.",
            UPLOAD_ERR_NO_FILE => "{$label} is required.",
            default => "{$label} failed to upload. Use JPG, PNG, WebP, or GIF under ".ini_get('upload_max_filesize').'.',
        };
    }
}
