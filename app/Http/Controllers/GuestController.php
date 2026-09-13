<?php

namespace App\Http\Controllers;

use App\Repositories\GuestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GuestController extends Controller
{
    public function store(Request $request)
    {
        $guest = GuestRepository::create([
            'unique_id' => $this->generateUniqueId()
        ]);
        return $this->json('Guest created successfully', [
            'guest_id' => $guest->unique_id
        ]);
    }

    private  function generateUniqueId(): string
    {
        do {
            $uniqueId = Str::random(32);
        } while (GuestRepository::query()->where('unique_id', $uniqueId)->exists());
        return $uniqueId;
    }
}
