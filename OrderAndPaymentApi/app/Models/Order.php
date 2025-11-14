<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'total_amount',
        'currency',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'integer',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    //Convierte el valor enviado en decimal (por ejemplo 503.50)
    public function setTotalAmountAttribute($value)
    {
        $this->attributes['total_amount'] = (int) round($value * 100);
    }

    //Devuelve el total de la orden en formato decimal
    public function getTotalAmountFormattedAttribute(): float
    {
        return (float) number_format($this->total_amount / 100, 2, '.', '');
    }
}
