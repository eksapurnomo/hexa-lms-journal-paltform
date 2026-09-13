<?php

namespace Database\Seeders;

use App\Repositories\LanguageRepository;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $folderPath = base_path('lang'); // actual folder path

        if (File::isDirectory($folderPath)) {
            $files = File::allFiles($folderPath);

            $fileNames = [];
            foreach ($files as $file) {
                $fileName = $file->getFilenameWithoutExtension();
                if ($fileName != 'installer_messages') {
                    $fileNames[] = $fileName;
                }
            }

            foreach ($fileNames as $fileName) {
                LanguageRepository::query()->updateOrCreate(
                    ['name' => $fileName],
                    ['title' => $fileName, 'created_at' => now()]
                );
            }

            $languageNames = LanguageRepository::query()->whereIn('name', $fileNames)->pluck('name')->toArray();
            foreach ($fileNames as $fileName) {
                if (count(array_keys($languageNames, $fileName)) > 1) {
                    LanguageRepository::query()
                        ->where('name', $fileName)
                        ->orderByDesc('id')
                        ->skip(1)
                        ->delete();
                }
            }
        }
    }
}
