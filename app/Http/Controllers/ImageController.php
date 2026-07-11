<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
  public function show(string $path)
  {
    abort_unless(Storage::disk('local')->exists($path), 404);

    $file = Storage::disk('local')->path($path);

    $mime = \Illuminate\Support\Facades\File::mimeType($file);

    return response()->file(
      $file,
      [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000',
      ]
    );
  }
}