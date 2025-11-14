<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'total_amount',
        'currency',
        'status',
    ];

    protected $casts = [
        'total_amout' => 'integer',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
