<?php

namespace App\Http\Controllers;

use App\Events\MailSendEvent;
use App\Http\Requests\StudentRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\AccountActivationRepository;
use App\Repositories\FcmDeviceTokenRepository;
use App\Repositories\GuestRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(StudentRegisterRequest $request)
    {
        $newUser = UserRepository::storeByStudentRequest($request);

        try {
            $token = JWTAuth::fromUser($newUser);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        if ($request->fcm_token) {
            FcmDeviceTokenRepository::create([
                'user_id' => $newUser->id,
                'token' => $request->fcm_token,
            ]);
        }

        $code = rand(1111, 9999);

        AccountActivationRepository::create([
            'user_id' => $newUser->id,
            'code' => $code,
            'valid_until' => now()->addHour(),
        ]);

        try {
            MailSendEvent::dispatch($code, $newUser->email);
        } catch (\Throwable $th) {
            //throw $th;
        }

        if ($request->guest_id) {
            $this->syncGuest($request->guest_id, $newUser);
        }

        $newUser->refresh();

        return $this->json('Registration successful', [
            'user' => UserResource::make($newUser),
            'token' => $token,
            'activation_code' => $code,
        ], 201);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required_without:phone',
            'phone' => 'required_without:email',
            'password' => 'required',
        ]);

        // $token = JWTAuth::attempt($validatedData);

        try {
            if (!$token = JWTAuth::attempt($validatedData)) {
                return $this->json('Invalid credentials', null, 403);
            }
        } catch (JWTException $e) {
            return $this->json('Could not create token', null, 500);
        }

        /** @var User */
        $user = JWTAuth::user(); // Fetch the authenticated user

        if ($user->is_admin || $user->hasRole('admin') || $user->instructor) {
            return $this->json('You are not a student, please sign up as a student', null, 403);
        }

        if ($request->fcm_token) {
            FcmDeviceTokenRepository::create([
                'user_id' => $user->id,
                'token' => $request->fcm_token,
            ]);
        }

        if ($request->guest_id) {
            $this->syncGuest($request->guest_id, $user);
        }

        return $this->json('Login successful', [
            'user'  => UserResource::make($user),
            'token' => $token,
        ]);
    }

    public function index()
    {
        return $this->json('User data found', [
            'user' => UserResource::make(auth()->user()),
        ]);
    }

    public function update(UserUpdateRequest $request)
    {
        /** @var User */
        $user = auth()->user();

        // Check current password if provided
        if ($request->current_password) {
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->json('password mismatch', ['current_password_error' => 'Current password is incorrect'], 401);
            }
        }

        // Update user data
        UserRepository::updateByRequest($request, $user);

        // Retrieve the updated user
        $updatedUser = UserRepository::find(auth()->user()->id);

        // Generate a new JWT token
        $token = JWTAuth::fromUser($updatedUser);

        return $this->json('User data updated', [
            'user' => UserResource::make($updatedUser),
            'token' => $token, // Use the JWT token
        ]);
    }

    public function updatePassword(Request $request)
    {
        /** @var User */
        $user = auth()->user();
        $request->validate(['password' => 'min:8|confirmed']);

        UserRepository::update($user, ['password' => Hash::make($request->password)]);

        return $this->json('Password updated', null, 200);
    }

    public function delete()
    {
        /** @var User */
        $user = auth()->user();
        $user->delete();
        return $this->json('User deleted', null, 200);
    }

    /**
     * Sync guest with user and delete guest.
     */
    private function syncGuest(string $guestId, User $user): void
    {
        $guest = GuestRepository::query()->where('unique_id', '=', $guestId)->first();

        if (!$guest) {
            return;
        }

        $user->favouriteCourses()->attach($guest->favouriteCourses->pluck('id'));
        $user->favouriteInstructors()->attach($guest->favouriteInstructors->pluck('id'));

        $guest->delete();
    }
}
