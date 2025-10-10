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
        'station_id',
        'date',
        'rainfall_amount',
        'rain_days',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /** 🏞️ Relasi: Data ini berasal dari satu stasiun */
    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    /** 📅 Akses label format Y-m */
    public function getMonthYearAttribute()
    {
        return $this->date ? $this->date->format('Y-m') : null;
    }

    /** 📅 Label bulan dalam format "Januari 2021" */
    public function getMonthYearLabelAttribute()
    {
        return $this->date
            ? $this->date->translatedFormat('F Y')
            : '-';
    }
}
