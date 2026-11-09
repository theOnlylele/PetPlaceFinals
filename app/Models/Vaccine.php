<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vaccine extends Model
{
    protected $fillable = ['vaccine_key', 'name', 'animal_type', 'pricing_method', 'fixed_price', 'is_active'];

    protected function casts(): array
    {
        return ['fixed_price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function doses(): HasMany
    {
        return $this->hasMany(VaccineDose::class)->orderBy('dose_number');
    }
}
