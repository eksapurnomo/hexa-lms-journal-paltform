<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Education;

class EducationRepository extends Repository
{
    public static function model()
    {
        return Education::class;
    }
}
