<?php

namespace App\Models;

use App\Models\Concerns\ChecksDeletionDependencies;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'name_bn', 'slug', 'logo', 'status', 'requested_by'])]
class Brand extends Model
{
    use ChecksDeletionDependencies, HasFactory, SoftDeletes;

    protected $attributes = ['status' => 'approved'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
