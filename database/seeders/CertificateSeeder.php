<?php

namespace Database\Seeders;

use App\Enum\MediaTypeEnum;
use App\Models\Media;
use App\Repositories\ManageCertificateRepository;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ManageCertificateRepository::query()->updateOrCreate(
            [
                'id' => 1,
            ],
            [
                'frame_id' => null,
                'site_logo_id' => null,
                'subsite_logo_id' => null,
                'author_signature_id' => null,
                'certificate_title' => 'Achievement',
                'certificate_short_text' => 'This Certificate is Proudly Presented to',
                'certificate_text' => 'This certificate is proudly presented for successfully completing the {course_name} Course with outstanding dedication and excellence. Your commitment to learning and achievement is truly commendable. Presented by Ready LMS.',
                'author_name' => 'Fahim Hossain Munna',
                'author_designation' => "Funder & CEO of LMS",
            ]
        );
    }
}
