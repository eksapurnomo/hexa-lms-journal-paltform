<?php

namespace App\Http\Controllers;

use App\Events\MailSendEvent;
use App\Models\User;
use App\Repositories\AccountActivationRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AccountActivationController extends Controller
{
    public function sendActivationCode()
    {
        if (auth()->user()->is_active) {
            return $this->json('Account already activated', [], 400);
        }

        $code = rand(1111, 9999);

        AccountActivationRepository::create([
            'user_id' => auth()->user()->id,
            'code' => $code,
            'valid_until' => now()->addHour(),
        ]);

        try {
            MailSendEvent::dispatch($code, auth()->user()->email);
        } catch (\Exception $e) {
        }

        return $this->json('Activation code sent', ['activation_code' => $code], 201);
    }

    public function activateAccount(Request $request)
    {
        if (auth()->user()->is_active) {
            return $this->json('Account already activated', [], 400);
        }

        $code = AccountActivationRepository::query()
            ->where('code', $request->code)
            ->where('user_id', auth()->user()->id)
            ->where('valid_until', '>=', now())
            ->first();

        if ($code) {
            /** @var User */
            $user = auth()->user();
            UserRepository::update($user, ['is_active' => true]);

            $code->delete();
            $token = JWTAuth::fromUser($user);

            return $this->json('Account activated', [
                'token' => $token,
            ], 200);
        }

        return $this->json('Invalid activation code', [], 400);
    }
}
