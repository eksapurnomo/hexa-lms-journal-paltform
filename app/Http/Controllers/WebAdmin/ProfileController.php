<?php

namespace App\Http\Controllers\WebAdmin;

use App\Enum\MediaTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\MediaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function profileImageUpdate(User $user, Request $request)
    {

        $profilePicture = $request->hasFile('profile_picture') ? MediaRepository::storeByRequest(
            $request->file('profile_picture'),
            'user/profile_picture',
            MediaTypeEnum::IMAGE
        ) : null;

        if ($profilePicture && $user->profilePicture && Storage::exists($user->profilePicture->src)) {
            Storage::delete($user->profilePicture->src);
        }

        $user->media_id = $profilePicture ? $profilePicture->id : null;
        $user->save();

        return back()->withSuccess('Profile picture updated');
    }
}
