<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Guest;

class GuestRepository extends Repository
{
    public static function model()
    {
        return Guest::class;
    }
}
