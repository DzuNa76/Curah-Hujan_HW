<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RainfallData extends Model
{
    use HasFactory;

    protected $table = 'rainfall_data';
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $keyType = 'string';

    protected $fillable = ['id', 'date', 'rainfall_amount', 'rain_days'];
}
