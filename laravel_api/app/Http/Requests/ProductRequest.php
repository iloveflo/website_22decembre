<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cho phép tất cả request qua middleware auth:sanctum rồi
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:150',
            'price'       => 'required|numeric|min:0',

            // các field optional:
            'description' => 'nullable|string',
            'status'      => 'nullable|in:active,inactive,out_of_stock',
            'sale_price'  => 'nullable|numeric|min:0',
            'sku'         => 'nullable|string|max:50',

            // mảng variants (có thể gửi hoặc không)
            'variants'                       => 'nullable|array',
            'variants.*.variant_attributes'  => 'nullable|array',
            'variants.*.sku'                 => 'nullable|string|max:50',
            'variants.*.quantity'            => 'nullable|integer|min:0',
            'variants.*.additional_price'    => 'nullable|numeric|min:0',
        ];
    }
}
