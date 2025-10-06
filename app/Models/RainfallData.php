<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RainfallData extends Model
{
    use HasFactory;

    protected $table = 'rainfall_data';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'date',
        'rainfall_amount',
        'rain_days',
        'created_at',
        'updated_at',
    ];

    // Cast date ke Carbon otomatis
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Accessor: mengembalikan 'Y-m' (mis. "2021-01") atau null
     */
    public function getMonthYearAttribute()
    {
        if ($this->date) {
            return $this->date->format('Y-m');
        }
        return null;
    }

    /**
     * Accessor: label seperti "Januari 2021" (atau '-' jika null)
     */
    public function getMonthYearLabelAttribute()
    {
        if ($this->date) {
            try {
                return $this->date->translatedFormat('F Y'); // membutuhkan locale jika mau bahasa Indonesia
            } catch (\Exception $e) {
                return $this->date->format('Y-m');
            }
        }
        return '-';
    }
}
