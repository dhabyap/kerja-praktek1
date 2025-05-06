<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama',
        'appartement_id',
    ];

    public function appartement()
    {
        return $this->belongsTo(Appartement::class);
    }

    

}
