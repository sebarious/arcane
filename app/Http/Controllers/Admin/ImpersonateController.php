<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auth\ImpersonationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonateController extends Controller
{
    public function __construct(private ImpersonationManager $impersonation) {}

    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $this->impersonation->stop($request);

        abort_unless($impersonatorId, 403, 'Not currently impersonating.');

        return redirect('/admin')->with('success', 'Impersonation ended.');
    }
}
