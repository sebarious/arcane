<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Separate from password_reset_tokens on purpose — the seller-approval email's
// "set your password" link is a first-time invite a store owner might not act
// on for days, not a self-service "I forgot my password" recovery (which stays
// short-lived for security). Isolating the table means each flow's expiry is
// checked unambiguously, with no risk of one broker's config being applied to
// a token the other one created — see config/auth.php's "seller_invite" broker
// and SellerApplicationApprover.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_invite_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_invite_tokens');
    }
};
