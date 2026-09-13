<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Experience;

class ExperienceRepository extends Repository
{
    public static function model()
    {
        return Experience::class;
    }
}
