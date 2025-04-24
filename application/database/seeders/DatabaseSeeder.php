<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Seed users
        \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Admin Tester',
            'email' => 'admin@puurrty.io',
            // added for dusk tests
            'password' => bcrypt('secret'),
        ]);

        // Seed events
        $events = \App\Models\Event::factory(3)->create(); // change number if needed

        // Seed tickets for each event
        foreach ($events as $event) {
            \App\Models\Ticket::factory(5)->create([
                'eventId' => $event->id
            ]);
        }
    }
}
