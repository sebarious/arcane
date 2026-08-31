<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BankDetailsController extends Controller
{
    public function show(Request $request)
    {
        $affiliate = $request->user()->affiliate;

        return Inertia::render('Affiliate/BankDetails', [
            'bankAccountName' => $affiliate->bank_account_name,
            'bankSortCode' => $affiliate->bank_sort_code,
            'bankAccountNumber' => $affiliate->bank_account_number,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'bank_account_name' => ['required', 'string', 'max:255'],
            // Loose UK-format validation — 6 digits, optionally dashed (12-34-56 or 123456).
            'bank_sort_code' => ['required', 'regex:/^\d{2}-?\d{2}-?\d{2}$/'],
            'bank_account_number' => ['required', 'regex:/^\d{8}$/'],
        ]);

        $request->user()->affiliate->update($data);

        return back()->with('success', 'Your bank details have been saved.');
    }
}
