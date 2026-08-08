<?php

namespace App\Http\Controllers\Debug;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ErrorPagePreviewController extends Controller
{
    public function __invoke(Request $request, int $status): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('admin')) {
            abort(403);
        }

        if (! in_array($status, [403, 404, 500, 503], true)) {
            abort(404);
        }

        return Inertia::render('Errors/Error', ['status' => $status]);
    }
}
