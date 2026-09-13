<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Enum\MediaTypeEnum;
use App\Http\Requests\StudentRegisterRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository extends Repository
{
    public static function model()
    {
        return User::class;
    }

    public static function storeByRequest(UserStoreRequest $request)
    {
        $profilePicture = $request->hasFile('profile_picture') ? MediaRepository::storeByRequest(
            $request->file('profile_picture'),
            'user/profile_picture',
            MediaTypeEnum::IMAGE
        ) : $profilePicture = MediaRepository::storeByPath(
            public_path('media/blank-user.png'), // Path to default image
            'user/profile_picture',
            MediaTypeEnum::IMAGE
        );



        return self::create([
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'name'     => $request->name,
            'media_id' => $profilePicture ? $profilePicture->id : null,
        ]);
    }
    public static function storeByStudentRequest(StudentRegisterRequest $request)
    {
        $profilePicture = $request->hasFile('profile_picture') ? MediaRepository::storeByRequest(
            $request->file('profile_picture'),
            'user/profile_picture',
            MediaTypeEnum::IMAGE
        ) : $profilePicture = MediaRepository::storeByPath(
            public_path('media/blank-user.png'), // Path to default image
            'user/profile_picture',
            MediaTypeEnum::IMAGE
        );



        return self::create([
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'name'     => $request->name,
            'media_id' => $profilePicture ? $profilePicture->id : null,
        ]);
    }

    public static function updateByRequest(UserUpdateRequest $request, User $user)
    {
        if ($user->profilePicture) {
            $profilePicture = $request->hasFile('profile_picture') ? MediaRepository::updateByRequest(
                $request->file('profile_picture'),
                $user->profilePicture,
                'user/profile_picture',
                MediaTypeEnum::IMAGE
            ) : $user->profilePicture;
        } else {
            $profilePicture = $request->hasFile('profile_picture') ? MediaRepository::storeByRequest(
                $request->file('profile_picture'),
                'user/profile_picture',
                MediaTypeEnum::IMAGE
            ) : null;
        }

        if ($user->signature) {
            $signature = $request->hasFile('signature') ? MediaRepository::updateByRequest(
                $request->file('signature'),
                $user->signature,
                'user/signature',
                MediaTypeEnum::IMAGE
            ) : $user->signature;
        } else {
            $signature = $request->hasFile('signature') ? MediaRepository::storeByRequest(
                $request->file('signature'),
                'user/signature',
                MediaTypeEnum::IMAGE
            ) : null;
        }

        if ($user->instructor) {
            InstructorRepository::find($user->instructor->id)->update([
                'signature_id' => $signature ? $signature->id : null,
            ]);
        }

        return self::update($user, [
            'phone'    => $request->phone ?? $user->phone,
            'gender'    => $request->gender ?? $user->gender,
            'about'    => $request->about ?? $user->about,
            'whatsapp'    => $request->whatsapp ?? $user->whatsapp,
            'birthday'    => $request->birthday ?? $user->birthday,
            'email'    => $request->email ?? $user->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'name'     => $request->name ?? $user->name,
            'media_id' => $profilePicture ? $profilePicture->id : null,
            'signature_id' => $signature ? $signature->id : null,
        ]);
    }
}
