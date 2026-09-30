<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureKioskUnlocked;
use App\Models\User;
use App\Services\Kiosk\KioskDailyPin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The kiosk role is a permissions boundary: it exists so shop-floor staff can
 * open the till and reach nothing else in the admin. These cover the boundary
 * itself, so a resource added later can't quietly fall outside it.
 */
class KioskStaffAccessTest extends TestCase
{
    use RefreshDatabase;

    /** Admin panel paths a kiosk user must never be served. */
    public static function adminPaths(): array
    {
        return [
            'dashboard' => ['/admin'],
            'inventory' => ['/admin/card-inventories'],
            'all inventory' => ['/admin/all-inventory'],
            'batches' => ['/admin/batches'],
            'stores' => ['/admin/stores'],
            'users' => ['/admin/users'],
            'rapid intake' => ['/admin/card-inventories/rapid'],
        ];
    }

    private function kioskUser(): User
    {
        Role::findOrCreate('kiosk', 'web');

        return User::factory()->create()->syncRoles(['kiosk']);
    }

    private function adminUser(): User
    {
        Role::findOrCreate('admin', 'web');

        return User::factory()->create()->syncRoles(['admin']);
    }

    #[DataProvider('adminPaths')]
    public function test_kiosk_staff_are_redirected_away_from_every_admin_page(string $path): void
    {
        $this->actingAs($this->kioskUser())
            ->get($path)
            ->assertRedirect(route('kiosk.access'));
    }

    #[DataProvider('adminPaths')]
    public function test_admins_still_reach_the_admin_panel(string $path): void
    {
        $response = $this->actingAs($this->adminUser())->get($path);

        // Any outcome except being bounced to the kiosk page: the point is
        // that the kiosk gate does not catch admins.
        $this->assertNotEquals(
            route('kiosk.access'),
            $response->headers->get('Location'),
            "An admin was redirected away from {$path}",
        );
    }

    public function test_kiosk_staff_can_see_their_access_page(): void
    {
        $this->actingAs($this->kioskUser())
            ->get(route('kiosk.access'))
            ->assertOk();
    }

    public function test_the_access_page_needs_the_role(): void
    {
        Role::findOrCreate('seller', 'web');
        $seller = User::factory()->create()->syncRoles(['seller']);

        $this->actingAs($seller)->get(route('kiosk.access'))->assertForbidden();

        // A guest is refused too (403 here rather than a login redirect —
        // the role middleware answers first, as on the seller routes).
        $this->assertTrue($this->get(route('kiosk.access'))->isSuccessful() === false);
    }

    public function test_apply_unlocks_the_till_without_typing_the_pin(): void
    {
        $this->actingAs($this->kioskUser())
            ->post(route('kiosk.access.apply'))
            ->assertRedirect(route('kiosk.index'));

        $this->assertSame(
            app(KioskDailyPin::class)->for(),
            session(EnsureKioskUnlocked::SESSION_KEY),
            'Applying should leave the browser unlocked for today',
        );
    }

    public function test_apply_is_refused_without_the_role(): void
    {
        $this->assertFalse($this->post(route('kiosk.access.apply'))->isSuccessful());
        $this->assertNull(session(EnsureKioskUnlocked::SESSION_KEY));
    }
}
