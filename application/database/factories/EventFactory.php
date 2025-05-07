<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'uuid'                 => Str::uuid()->toString(),
            'name'                 => $this->faker->sentence(3),
            'location'             => $this->faker->city,
            'eventDate'            => now()->toDateString(),
            'eventStart'           => now()->format('H:i:s'),
            'eventEnd'             => now()->addHours(2)->format('H:i:s'),
            'startDateTime'        => now()->toDateTimeString(),
            'endDateTime'          => now()->addDay()->toDateTimeString(),
            'hodlAsset'            => false,
            'policyIds'            => [$this->faker->sha1],
            'nonceValidForMinutes' => 30,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Event $event) {
            Ticket::factory()->create([
                'eventId' => $event->id,
            ]);
        });
    }
}
