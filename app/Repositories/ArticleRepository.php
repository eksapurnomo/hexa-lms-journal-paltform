<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Article;

class ArticleRepository extends Repository
{
    public static function model()
    {
        return Article::class;
    }
}
