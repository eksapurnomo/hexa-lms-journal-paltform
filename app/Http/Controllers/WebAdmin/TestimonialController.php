<?php

namespace App\Http\Controllers\WebAdmin;

use App\Enum\MediaTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\TestimonialStoreRequest;
use App\Http\Requests\TestimonialUpdateRequest;
use App\Models\Testimonial;
use App\Repositories\MediaRepository;
use App\Repositories\TestimonialRepository;
use Illuminate\Http\Request;
use PHPUnit\Event\Code\Test;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;

        $testimonials = TestimonialRepository::query()->when($search, function ($query) use ($search) {
            $query->where('name', 'like', '%' . $search . '%')->OrWhere('designation', 'like', '%' . $search . '%');
        })->withTrashed()->latest('id')->paginate(15)->withQueryString();
        return view('testimonial.index', [
            'testimonials' => $testimonials,
        ]);
    }

    public function create()
    {
        return view('testimonial.create');
    }

    public function store(TestimonialStoreRequest $request)
    {
        if (app()->isLocal()) {
            return to_route('testimonial.index')->with('error', 'Testimonial not created in demo mode');
        }

        TestimonialRepository::storeByRequest($request);

        return to_route('testimonial.testimonial')->withSuccess('Testimonial created successfully.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('testimonial.edit', [
            'testimonial' => $testimonial,
        ]);
    }

    public function update(TestimonialUpdateRequest $request, Testimonial $testimonial)
    {
        TestimonialRepository::updateByRequest($request, $testimonial);
        return to_route('testimonial.index')->withSuccess('Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();
        $testimonial->is_active = false;
        $testimonial->save();
        return to_route('testimonial.index')->withSuccess('Testimonial deleted successfully.');
    }

    public function restore($testimonial)
    {
        TestimonialRepository::query()->withTrashed()->find($testimonial)->restore();
        return to_route('testimonial.index')->withSuccess('Testimonial restored successfully.');
    }
}
