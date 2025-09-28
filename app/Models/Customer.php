<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
    ];

    /**
     * Зв'язок з угодами
     */
    public function deals()
    {
        return $this->hasMany(Deal::class, 'customer_id');
    }

    /**
     * Отримати повне ім'я клієнта
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
