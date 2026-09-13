<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\AccountActivation;

class AccountActivationRepository extends Repository
{
    public static function model()
    {
        return AccountActivation::class;
    }
}
