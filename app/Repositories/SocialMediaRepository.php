<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\SocialMedia;

class SocialMediaRepository extends Repository
{
    public static function model()
    {
        return SocialMedia::class;    
    }
}