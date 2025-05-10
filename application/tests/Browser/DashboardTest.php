<?php
namespace Tests\Browser;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_admin_dashboard_sees_all_components()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('dashboard.index'));

        $response->assertStatus(200);
        $response->assertSee('Manage Users');
        $response->assertSee('Manage Events');
    }

    /** @test */
    public function test_staff_sees_only_staff_components()
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff);

        $response = $this->get(route('dashboard.index'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Scan Tickets');
        $response->assertDontSee('Manage Users');
        $response->assertDontSee('Manage Events');
    }
}
