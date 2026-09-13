<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Enum\MediaTypeEnum;
use App\Http\Requests\ManageCertificateRequest;
use App\Models\ManageCertificate;

class ManageCertificateRepository extends Repository
{
    public static function model()
    {
        return ManageCertificate::class;
    }
    public static function updateByRequest(ManageCertificateRequest $request, $certificate)
    {
        $siteLogo = $request->hasFile('site_logo_id') ? MediaRepository::updateOrCreateByRequest(
            $request->file('site_logo_id'),
            'certificate/site_logo',
            $certificate?->siteLogo,
            MediaTypeEnum::IMAGE
        ) : $certificate?->siteLogo;

        $subSiteLogo = $request->hasFile('subsite_logo_id') ? MediaRepository::updateOrCreateByRequest(
            $request->file('subsite_logo_id'),
            'setting/sub_site_logo',
            $certificate?->subSiteLogo,
            MediaTypeEnum::IMAGE
        ) : $certificate?->subSiteLogo;


        $authSignature = $request->hasFile('author_signature_id') ? MediaRepository::updateOrCreateByRequest(
            $request->file('author_signature_id'),
            'setting/author_signature',
            $certificate?->authSignature,
            MediaTypeEnum::IMAGE
        ) : $certificate?->authSignature;


        return self::query()->updateOrCreate([
            'id' => $certificate?->id
        ], [
            'frame_id' => $request->selected_frame ?? $certificate?->frame_id,
            'site_logo_id' => $siteLogo ? $siteLogo->id : null,
            'subsite_logo_id' => $subSiteLogo ? $subSiteLogo->id : null,
            'author_signature_id' => $authSignature ? $authSignature->id : null,
            'certificate_title' => $request->certificate_title ?? $certificate?->certificate_title,
            'certificate_short_text' => $request->certificate_short_text ?? $certificate?->certificate_short_text,
            'certificate_text' => $request->certificate_text ?? $certificate?->certificate_text,
            'author_name' => $request->author_name ?? $certificate?->author_name,
            'author_designation' => $request->author_designation ?? $certificate?->author_designation,
        ]);
    }
}
