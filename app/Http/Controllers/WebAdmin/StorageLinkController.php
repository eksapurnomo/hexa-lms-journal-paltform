<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class StorageLinkController extends Controller
{
    public function linkStorage()
    {
        try {
            Artisan::call('storage:link');
            return back()->with('success', 'Storage linked is successfully.');
        } catch (\Throwable $th) {
            return back()->with('error', 'Storage not linked beacause ' . $th->getMessage());
        }
    }
}
