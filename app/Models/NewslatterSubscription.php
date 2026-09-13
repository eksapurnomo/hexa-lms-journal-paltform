<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewslatterSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'newslatter_subscriptions';

    protected $guarded = [
        'id',
    ];
}