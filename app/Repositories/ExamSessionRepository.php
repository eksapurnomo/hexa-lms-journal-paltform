<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\ExamSession;

class ExamSessionRepository extends Repository
{
    public static function model()
    {
        return ExamSession::class;
    }
}
