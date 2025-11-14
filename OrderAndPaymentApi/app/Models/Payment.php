<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'amount',
        'status',
        'payment_method',
        'attempt_number',
        'external_reference',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getAmountFormattedAttribute():float
    {
        return (float) number_format($this->amount / 100, 2, '.', '');
    }


}
