<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\ManageCertificateRepository;
use App\Repositories\UserRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Exception;
use Illuminate\Support\Facades\Crypt;

class CertificateController extends Controller
{
    public function index()
    {
        $courses = CourseRepository::query()
            ->where('certificate_available', '=', true)
            ->whereHas('enrollments', function ($query) {
                return $query->where('user_id',  auth()->id());
            })
            ->get();

        return $this->json('Certificates found', [
            'certificate_courses' => CourseResource::collection($courses),
        ]);
    }

    public function show(Course $course)
    {
        $enrollment = EnrollmentRepository::query()
            ->where('course_id', '=', $course->id)
            ->where('user_id', '=', auth()->user()->id)
            ->first();

        if (!$enrollment) {
            return $this->json('Enrollment required', null, 403);
        }

        if (!$enrollment->course->certificate_available) {
            return $this->json('Certificate not available', null, 404);
        }

        EnrollmentRepository::update($enrollment, ['is_certificate_downloaded' => true,]);

        $url = [
            'user_name' => $enrollment->certificate_user_name,
            'user_id' => $enrollment->user_id,
            'course_id' => $enrollment->course->id,
            'course_title' => $enrollment->course->title,
            'instructor' => $enrollment->course->instructor->user,
        ];

        $encryptData = encrypt($url);
        $encodedUrl = urlencode($encryptData);

        return $this->json('certificate url', [
            'url' => route('download.certificate', $encodedUrl),
        ]);
    }

    public function downloadCertificate($encodeData)
    {
        try {
            $bycryptData = decrypt($encodeData);
            $userName = $bycryptData['user_name'];

            return $this->generatePdf(
                $userName,
                $bycryptData['course_title'],
                $bycryptData['instructor'],
                $bycryptData['course_id'],
                $bycryptData['user_id'],
            );
        } catch (Exception $th) {
            return $this->json($th->getMessage(), [], 422);
        }
    }

    public function showCertificate($courseId, $userId)
    {
        try {
            $enrollment = EnrollmentRepository::query()
                ->where('course_id', '=', $courseId)
                ->where('user_id', '=', $userId)
                ->where('course_progress', '=', 100.00)
                ->first();

            if (!$enrollment->course->certificate_available) {
                return $this->json('Certificate not available', null, 404);
            }

            if (!$enrollment) {
                return $this->json('certificate does not exist', [], 422);
            }

            $appName = config('app.name');

            return $this->generatePdfForCheck(
                $enrollment->certificate_user_name,
                $enrollment->course->title,
                $enrollment->course->instructor->user,
                $enrollment->course_id,
                $enrollment->user_id,
            );
        } catch (Exception $th) {
            return $this->json($th->getMessage(), [], 422);
        }
    }

    public function generatePdf($studentName, $courseTitle, $instructor, $courseId, $userId)
    {
        $instructorTitle = InstructorRepository::query()->where('user_id', $instructor->id)->first();
        $certificate = ManageCertificateRepository::query()->latest('id')->first();
        $appName = config('app.name');
        $url = route('show.certificate', [$courseId, $userId]);

        $qrCode = new EndroidQrCode($url);
        $qrCode->setSize(100);

        $writer = new PngWriter;
        $qrCodeImage = $writer->write($qrCode)->getDataUri();


        $pdf = Pdf::loadView('enrollment.certificate', [
            'studentName' => $studentName,
            'courseTitle' => $courseTitle,
            'appName' => $appName,
            'certificate' => $certificate,
            'instructor' => $instructor,
            'instructorTitle' => $instructorTitle->title,
            'instructorSignature' => $instructorTitle->user->signaturePath,
            'qrCodeImage' => $qrCodeImage
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download("$studentName" . ".pdf");
    }


    public function generatePdfForCheck($studentName, $courseTitle, $instructor, $courseId, $userId)
    {
        $instructorTitle = InstructorRepository::query()->where('user_id', $instructor->id)->first();
        $instructorDetails = UserRepository::query()->where('id', $instructor->id)->first();
        $certificate = ManageCertificateRepository::query()->latest('id')->first();
        $appName = config('app.name');

        $url = route('show.certificate', [$courseId, $userId]);
        $qrCode = new EndroidQrCode($url);
        $qrCode->setSize(100);

        $writer = new PngWriter;
        $qrCodeImage = $writer->write($qrCode)->getDataUri();

        $pdf = Pdf::loadView('enrollment.certificate', [
            'studentName' => $studentName,
            'courseTitle' => $courseTitle,
            'appName' => $appName,
            'certificate' => $certificate,
            'instructor' => $instructorDetails,
            'instructorTitle' => $instructorTitle->title,
            'instructorSignature' => $instructorTitle->user->signaturePath,
            'qrCodeImage' => $qrCodeImage
        ]);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("$studentName" . ".pdf");
    }
}
