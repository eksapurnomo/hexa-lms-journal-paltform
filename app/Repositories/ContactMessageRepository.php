<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\ContactMessage;

class ContactMessageRepository extends Repository
{
    public static function model()
    {
        return ContactMessage::class;
    }
}
