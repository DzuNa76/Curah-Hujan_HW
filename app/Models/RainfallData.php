<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RainfallData extends Model
{
    use HasFactory;

    protected $table = 'rainfall_data'; // nama tabel di MySQL
    protected $fillable = ['date', 'rainfall_amount', 'rain_days'];
}
