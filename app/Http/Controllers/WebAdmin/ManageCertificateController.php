<?php

namespace App\Http\Controllers\WebAdmin;

use App\Enum\MediaTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ManageCertificateRequest;
use App\Models\Frame;
use App\Models\ManageCertificate;
use App\Repositories\FrameRepository;
use App\Repositories\ManageCertificateRepository;
use Illuminate\Http\Request;

class ManageCertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $frames = FrameRepository::query()->get();
        $certificate = ManageCertificateRepository::query()->latest('id')->first();
        return view('certificateConfigaration.index', compact('certificate','frames'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ManageCertificateRequest $request)
    {
        $certificate = ManageCertificateRepository::query()->latest('id')->first();

        if ($request->hasFile('frame_id')) {
            FrameRepository::storeByRequest(
                $request->file('frame_id'),
                'frame',
                MediaTypeEnum::IMAGE
            );

            return to_route('certificate.index')->withSuccess('Frame updated');
        }

        ManageCertificateRepository::updateByRequest($request, $certificate);

        return to_route('certificate.index')->withSuccess('Configaration updated');
    }

    public function delete($id)
    {
        $frame = Frame::findOrFail($id);
        $frame->delete();
    	return to_route('certificate.index')->withSuccess('Frame deleted successfully');
    }
}
