<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\NewslatterSubscription;

class NewslatterSubscriptionRepository extends Repository
{
    public static function model()
    {
        return NewslatterSubscription::class;    
    }
}