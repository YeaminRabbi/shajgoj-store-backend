<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'name_bn', 'slug', 'logo', 'status', 'requested_by'])]
class Brand extends Model
{
    use HasFactory;

    protected $attributes = ['status' => 'approved'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
