<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\NotificationInstance;

class NotificationInstanceRepository extends Repository
{
    public static function model()
    {
        return NotificationInstance::class;
    }
}
