<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Frame;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FrameRepository extends Repository
{
    public static function model()
    {
        return Frame::class;
    }

    public static function storeByRequest(UploadedFile $file, $path, $type = null): Frame
    {
        $src = Storage::put('/' . trim($path, '/'), $file, 'public');

        return self::create([
            'extension' => $file->extension(),
            'src' => $src,
            'path' => $path,
            'type' => $type,
        ]);
    }

    // this for local path file update

    public static function storeByPath($filePath, $path, $type = null): Frame
    {
        // Read the file content from the provided path
        $fileContents = file_get_contents($filePath);
        $fileName = basename($filePath); // Get the filename

        // Use Storage to put the file content into the specified directory
        $src = Storage::put('/' . trim($path, '/') . '/' . $fileName, $fileContents, 'public');

        // Return a new Media record with the stored file details
        return self::create([
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION), // Get the extension from file path
            'src' => $src,
            'path' => $path,
            'type' => $type,
        ]);
    }

    public static function updateByRequest(UploadedFile $file, Frame $frame, $path, $type = null): Frame
    {
        $src = Storage::put('/' . trim($path, '/'), $file, 'public');

        if (Storage::exists($frame->src)) {
            Storage::delete($frame->src);
        }

        self::update($frame, [
            'extension' => $file->extension(),
            'src' => $src,
            'path' => $path,
            'type' => $type,
        ]);

        return $frame;
    }

    public static function updateOrCreateByRequest(UploadedFile $file, $path, $frame = null, $type = null): Frame
    {
        $src = Storage::put('/' . trim($path, '/'), $file, 'public');

        if ($frame && Storage::exists($frame->src)) {
            Storage::delete($frame->src);
        }

        $frame = self::query()->updateOrCreate([
            'id' => $frame?->id ?? 0
        ], [
            'extension' => $file->extension(),
            'src' => $src,
            'path' => $path,
            'type' => $type,
        ]);

        return $frame;
    }
}