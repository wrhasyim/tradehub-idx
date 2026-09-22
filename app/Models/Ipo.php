<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 
        'company_name', 
        'status', 
        'offering_date'
    ];
}