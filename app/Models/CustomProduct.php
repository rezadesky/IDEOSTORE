<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomProduct extends Model
{
    protected $fillable = [
        'name',
        'variant',
        'category',
        'price',
        'normal_price',
        'stock',
        'type',
        'description',
        'image',
        'status',
    ];

    public function toCatalogItem(): array
    {
        return [
            'variant_id' => 'cp_' . $this->id,
            'product_id' => 'cp_' . $this->id,
            'product' => $this->name,
            'variant' => $this->variant ?: 'Standar',
            'category' => $this->category,
            'price' => $this->price,
            'cost_price' => $this->price,
            'sell_price' => $this->price,
            'normal_price' => $this->normal_price ?: $this->price,
            'profit' => 0,
            'stock' => $this->stock,
            'type' => $this->type ?: 'manual',
            'image' => $this->image,
            'image_original' => $this->image,
            'description' => $this->description,
            'status' => $this->status,
            'is_custom' => true,
        ];
    }
}
