<?php

namespace App\Http\Controllers;

use App\Http\Resources\TestimonialResource;
use App\Repositories\TestimonialRepository;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = TestimonialRepository::query()->where('is_active', true)->latest('id')->get();

        return $this->json('all testimonials data', ['testimonials' => TestimonialResource::collection($testimonials)], 200);
    }
}
