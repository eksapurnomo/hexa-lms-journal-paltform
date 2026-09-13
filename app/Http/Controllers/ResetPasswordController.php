<?php

namespace App\Http\Controllers;

use App\Events\MailSendEvent;
use App\Http\Resources\UserResource;
use App\Repositories\ResetPasswordRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ResetPasswordController extends Controller
{
    public function index(Request $request)
    {
        if(!$request->email){
            return $this->json('email field is required!', 500);
        }

        $user = UserRepository::query()->where('email', $request->email)->first();
        $otp = rand(1111, 9999);

        if (!$user) {
            return $this->json('this email does not exists on database', 500);
        }

        ResetPasswordRepository::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'valid_until' => now()->addHour(),
        ]);

        if (config('app.app_env') == 'production') {
            MailSendEvent::dispatch($otp, $user->email);
        }

        return $this->json('OTP sent', ['otp' => $otp], 201);

    }

    public function validateOtp(Request $request)
    {
        $user = UserRepository::query()->where('email', $request->email)->first();

        if ($user) {
            $otp = ResetPasswordRepository::query()
                ->where('otp', $request->otp)
                ->where('user_id', $user->id)
                ->where('valid_until', '>=', now())
                ->first();

            if ($otp) {
                $otp->delete();
                $token = JWTAuth::fromUser($user);

                return $this->json('OTP validated', [
                    'token' => $token,
                    'user' => UserResource::make($user),
                ], 200);
            }
        }

        return $this->json('Invalid OTP', [], 400);
    }
}
