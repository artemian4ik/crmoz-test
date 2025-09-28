<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'customer_id',
        'source',
    ];

    /**
     * Зв'язок з клієнтом
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
