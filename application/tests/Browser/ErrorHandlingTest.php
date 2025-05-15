<?php

namespace Tests\Browser;

use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    public function test_404_page_loads_on_bad_route()
    {
        $this->get('/non-existent-route')
            ->assertStatus(404);
    }

    public function test_403_error_for_unauthorized_admin_area_access()
    {
        $this->get(route('admin.manage-events.index'))
            ->assertStatus(403);
    }
}
