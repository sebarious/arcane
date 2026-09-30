<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Shop-floor staff who open the kiosk and nothing else. Created here
        // as well as in RoleSeeder so it reaches an existing deployment —
        // the seeder only runs when someone runs it.
        Role::firstOrCreate(['name' => 'kiosk', 'guard_name' => 'web']);
    }

    public function down(): void
    {
        // Only if nobody holds it — dropping a role out from under live
        // accounts would silently lock those people out.
        $role = Role::where('name', 'kiosk')->where('guard_name', 'web')->first();

        if ($role && $role->users()->doesntExist()) {
            $role->delete();
        }
    }
};
