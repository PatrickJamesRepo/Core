<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;
use App\Models\User;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'eventId' => Event::factory(), // will create related event
            'policyId' => $this->faker->regexify('[A-Fa-f0-9]{64}'),
            'assetId' => $this->faker->regexify('[A-Fa-f0-9]{64}'),
            'stakeKey' => $this->faker->regexify('[A-Fa-f0-9]{64}'),
            'signatureNonce' => Uuid::uuid4()->getBytes(),
            'ticketNonce' => Uuid::uuid4()->getBytes(),
            'isCheckedIn' => false,
            'signature' => null,
            'checkInTime' => null,
            'checkInUser' => null, // or: User::factory()
        ];
    }
}
