<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appartement extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi: Appartement memiliki banyak unit
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    // Relasi tidak langsung: Appartement punya banyak bookings lewat units
    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, Unit::class);
    }

    // Relasi tidak langsung: Appartement punya banyak transactions lewat units
    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Unit::class);
    }
}
