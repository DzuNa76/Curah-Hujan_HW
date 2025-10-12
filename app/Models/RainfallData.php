<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RainfallData extends Model
{
    use HasFactory;

    protected $table = 'rainfall_data';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',              // Kode bulan (mis: Jan-2025)
        'station_id',      // FK ke tabel stations
        'date',            // Tanggal awal bulan
        'rainfall_amount', // Curah hujan (mm)
        'rain_days',       // Hari hujan
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relasi ke stasiun
    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function getMonthYearAttribute()
    {
        return $this->date ? $this->date->format('Y-m') : null;
    }

    public function getMonthYearLabelAttribute()
    {
        if ($this->date) {
            try {
                return $this->date->translatedFormat('F Y');
            } catch (\Exception $e) {
                return $this->date->format('Y-m');
            }
        }
        return '-';
    }
}
