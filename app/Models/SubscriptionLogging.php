<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionLogging extends Model
{
    use HasFactory;
    protected $table = 'subscription_logging';

    protected $fillable = [
        'type',
        'payload',
        'merchant_id',
        'subscription_id',
        'msisdn',
        'operator_id',
    ];
}
