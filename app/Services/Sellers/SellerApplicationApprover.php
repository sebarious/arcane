<?php

namespace App\Services\Sellers;

use App\Mail\SellerApprovedMail;
use App\Models\SellerApplication;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class SellerApplicationApprover
{
  public function approve(SellerApplication $application, User $admin): User
  {
    return DB::transaction(function () use ($application, $admin) {
      if ($application->status === 'approved' && $application->user_id) {
        return $application->user;
      }

      $randomPassword = Str::password(24);

      $user = User::create([
        'name'     => $application->contact_name,
        'email'    => $application->contact_email,
        'password' => Hash::make($randomPassword),
      ]);

      $user->assignRole('seller');

      $slug = Str::slug($application->business_name);
      $originalSlug = $slug;
      $counter = 2;

      while (Store::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
      }

      $store = Store::create([
        'user_id'             => $user->id,
        'slug'                => $slug,
        'name'                => $application->business_name,
        'contact_email'       => $application->contact_email,
        'phone'               => $application->phone,
        'address_line_1'      => $application->address_line_1,
        'address_line_2'      => $application->address_line_2,
        'city'                => $application->city,
        'postcode'            => $application->postcode,
        'country'             => $application->country,
        'vat_number'          => $application->vat_number,
        'public_page_enabled' => false,
        'status'              => 'active',
      ]);

      $application->update([
        'user_id'             => $user->id,
        'status'              => 'approved',
        'reviewed_by_user_id' => $admin->id,
        'reviewed_at'         => now(),
      ]);

      // Create a password reset token manually
      $token = Password::broker()->createToken($user);

      $resetUrl = url(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
      ], false));

      Mail::to($user->email)->send(
        new SellerApprovedMail($user, $store, $resetUrl)
      );

      return $user;
    });
  }
}
