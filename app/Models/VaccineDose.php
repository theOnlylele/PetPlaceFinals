<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaccineDose extends Model
{
    protected $fillable = ['vaccine_id', 'dose_number', 'price'];
    protected function casts(): array { return ['price' => 'decimal:2']; }
    public function vaccine(): BelongsTo { return $this->belongsTo(Vaccine::class); }
}
