<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\UserContentView;

class UserContentViewRepository extends Repository
{
    public static function model()
    {
        return UserContentView::class;
    }
}
