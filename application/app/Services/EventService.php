<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Exceptions\AppException;

class EventService {

    public function getEventList($fullInfo = false): Collection
    {
        $selectColumns = ['uuid', 'name', 'policyIds'];

        if ($fullInfo) {
            $selectColumns = array_merge($selectColumns, [
                'id', 'nonceValidForMinutes', 'hodlAsset', 'startDateTime', 'endDateTime',
            ]);
        }

        return Event::all($selectColumns);
    }

    /**
     * @throws AppException|ValidationException
     */
    public function save(array $payload): void
    {
        $event = null;
        if (!empty($payload['event_id']) && !$event = $this->findById($payload['event_id'])) {
            throw new AppException(trans('Event not found'));
        }

        $payload['policyIds'] = array_filter(preg_split("/\r\n|\n|\r/", $payload['policyIds']));

        $validationRules = [
            'name'                 => ['required', 'min:3'],
            'policyIds'            => ['required', 'array', 'min:1'],
            'endDateTime'          => ['required', 'date'],
            'startDateTime'        => ['date'],
            'hodlAsset'            => ['integer'],
            'nonceValidForMinutes' => ['required', 'integer', 'min:5'],
            'location'             => ['string'],
            'eventStart'           => ['string'],
            'eventEnd'             => ['string'],
            'eventDate'            => ['date'],
            'image'                => ['string'],
        ];

        $validator = Validator::make($payload, $validationRules);

        if ($validator->fails()) {
            throw new AppException(sprintf('%s: %s', trans('validation errors'), implode(' ', $validator->errors()->all())));
        }

        if (!$event) {
            $event = new Event;
        }

        $validPayload = $validator->validated();
        $event->fill($validPayload);
        $event->save();

        // Conditional Ticket Generation Logic
        if (isset($payload['hodlAsset']) && $payload['hodlAsset'] == true) {
            $this->generateTickets($event, 5);  // Generate 5 tickets for this event
        }
    }

    private function generateTickets(Event $event, int $ticketCount)
    {
        for ($i = 0; $i < $ticketCount; $i++) {
            Ticket::create([
                'event_id' => $event->id,
                'policy_id' => 'example_policy_id', // Replace with logic for policies
            ]);
        }
    }

    public function findById(int $eventId): ?Event
    {
        return Event::where('id', $eventId)->first();
    }

    public function findByUUID(string $uuid): ?Event
    {
        return Event::where('uuid', $uuid)->first();
    }
}
