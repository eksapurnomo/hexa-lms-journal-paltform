<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\ResetPassword;

class ResetPasswordRepository extends Repository
{
    public static function model()
    {
        return ResetPassword::class;
    }
}
