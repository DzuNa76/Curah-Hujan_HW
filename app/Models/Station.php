<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = ['station_name', 'village_id'];

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function rainfallData()
    {
        return $this->hasMany(RainfallData::class);
    }
}
