<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\VerifyOtp;

class VerifyOtpRepository extends Repository
{
    public static function model()
    {
        return VerifyOtp::class;
    }
}