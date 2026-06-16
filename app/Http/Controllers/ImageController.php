<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
  public function show(string $path)
  {
    abort_unless(Storage::disk('local')->exists($path), 404);

    return response()->file(
      Storage::disk('local')->path($path)
    );
  }
}