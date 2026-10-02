<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'household_id',
    'nik',
    'name',
    'gender',
    'birth_place',
    'birth_date',
    'religion',
    'education',
    'job',
    'marital_status',
    'family_relation',
    'occupancy_status',
    'phone',
])]
class HouseholdMember extends Model
{
    use HasFactory;

    public const GENDERS = [
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
    ];

    public const RELATIONS = [
        'Kepala Keluarga',
        'Suami',
        'Istri',
        'Anak',
        'Orang Tua',
        'Mertua',
        'Menantu',
        'Cucu',
        'Famili Lain',
        'Lainnya',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'immutable_date',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
