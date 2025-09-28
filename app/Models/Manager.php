<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'deals_count',
    ];

    public function incrementDealsCount()
    {
        $this->increment('deals_count');
    }
}
