<?php

namespace App\Http\Controllers;

use App\Http\Resources\InstructorResource;
use App\Http\Resources\PageResource;
use App\Http\Resources\PaymentGatewayResource;
use App\Models\PaymentGateway;
use App\Models\SocialMedia;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\LanguageRepository;
use App\Repositories\PageRepository;
use App\Repositories\SettingRepository;
use App\Repositories\SocialMediaRepository;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index(Request $request)
    {
        $mostValuableCoursePrice = (int) CourseRepository::query()->orderBy('price', 'desc')->first()?->price;
        $setting = SettingRepository::query()->first();

        $socialMedia = SocialMediaRepository::query()->whereNotNull('url')->get();
        $instructors = InstructorRepository::query()->inRandomOrder()->limit(4)->get();

        return $this->json('Master info found', [
            'master' => [
                'name' => config('app.name'),
                'mode' => config('app.env'),
                'logo' => $setting->logoPath,
                'favicon' => $setting->faviconPath,
                'footer' => $setting->footerPath,
                'scaner' => $setting->scanerPath,
                'currency_symbol' => config('app.currency_symbol'),
                'currency' => config('app.currency'),
                'default_language' => config('app.locale'),
                'minimum_amount' => config('app.minimum_amount'),
                'currency_position' => $setting->currency_position,
                'timezone' => config('app.timezone'),
                'credit_text' => $setting->footer_text,
                'min_course_price' => 0,
                'max_course_price' => 0 == $mostValuableCoursePrice ? 1_000 : $mostValuableCoursePrice,
                'payment_methods' => PaymentGatewayResource::collection(PaymentGateway::query()->where('is_active', '=', true)->get()),
                'pages' => PageResource::collection(PageRepository::query()->get()),
                'total_courses' =>  CourseRepository::getAll()->count(),
                'total_instructors' =>  InstructorRepository::getAll()->count(),
                'total_enrollments' =>  EnrollmentRepository::getAll()->count(),
                'footer_contact' => $setting->footer_contact_number,
                'footer_email' => $setting->footer_support_mail,
                'footer_description' => $setting->footer_description,
                'footer_social_icons' => $socialMedia,
                'footer_apple_link' => $setting->app_store_url,
                'footer_google_link' => $setting->play_store_url,
                'instructors' => InstructorResource::collection($instructors),
                'total_instructors' => InstructorRepository::query()->count(),
                'total_featured_instructors' => InstructorRepository::query()->where('is_featured', '=', true)->count(),
                'languages' => LanguageRepository::query()->get(),
            ],
        ]);
    }

    public function checkDeviceAndRedirect(Request $request)
    {
        $userAgent = $request->header('User-Agent');

        if (stripos($userAgent, 'Android') !== false) {
            return redirect('https://play.google.com/store');
        }

        if (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            return redirect('https://www.apple.com/app-store');
        }

        return redirect('/');
    }
}
