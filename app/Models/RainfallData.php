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

    protected $fillable = ['id', 'date', 'month', 'year', 'rainfall_amount', 'rain_days'];

    // Jika ada kolom date, cast otomatis
    protected $casts = [
        'date' => 'date',
    ];

    // Accessor -> mengembalikan 'Y-m' (mis. 2024-12) atau null
    public function getMonthYearAttribute()
    {
        if (!empty($this->month) && !empty($this->year)) {
            return $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT);
        }

        if (!empty($this->date)) {
            return $this->date->format('Y-m');
        }

        return null;
    }

    // Accessor -> label yang sudah terformat 'Desember 2024' (handle null)
    public function getMonthYearLabelAttribute()
    {
        $my = $this->month_year; // memanggil accessor di atas
        if ($my) {
            try {
                return Carbon::createFromFormat('Y-m', $my)->translatedFormat('F Y');
            } catch (\Exception $e) {
                return $my; // fallback
            }
        }
        return '-';
    }
}
