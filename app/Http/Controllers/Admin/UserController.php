<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('items_per_page', 10);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;

        $users = User::all()->skip($skip)->take($perPage)->values();

        return $this->json($users ? 'Users found' : 'No user found', [
            'total_items' => count($users),
            'users' => UserResource::collection($users),
        ], $users ? 200 : 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->json('Current password is incorrect', null, 422);
        }

        UserRepository::updateByRequest($request, $user);
        $updatedUser = UserRepository::find($user->id);

        return $this->json('User data updated', [
            'user' => UserResource::make($updatedUser),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->json('User deleted successfully');
    }
}
