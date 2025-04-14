<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $now = Carbon::now();

        return [
            'uuid' => Str::uuid()->toString(),
            'name' => $this->faker->sentence(3),
            'policyIds' => [$this->faker->sha1],
            'nonceValidForMinutes' => $this->faker->numberBetween(10, 60),
            'hodlAsset' => $this->faker->boolean(),
            'startDateTime' => $now->toDateTimeString(),
            'endDateTime' => $now->clone()->addDays(30)->toDateTimeString(),
        ];
    }
}
