<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class ManageEventsController extends Controller
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function store(Request $request): RedirectResponse
    {
        // Laravel validation will redirect back with field errors
        $data = $request->validate([
            'name'                 => 'required|min:3',
            'location'             => 'nullable|string',
            'eventDate'            => 'required|date',
            'eventStart'           => 'required|string',
            'eventEnd'             => 'required|string',
            'startDateTime'        => 'required|date',
            'endDateTime'          => 'required|date|after:startDateTime',
            'hodlAsset'            => 'boolean',
            'policyIds'            => 'required|array|min:1',
            'nonceValidForMinutes' => 'required|integer|min:5',
            'image'                => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('public');
        }

        try {
            $this->eventService->save($data);
            return redirect()
                ->route('admin.manage-events.index')        // correct prefix
                ->with('status', __('Event created'));
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => __('Failed to save event')]);
        }
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'name'                 => 'required|min:3',
            'location'             => 'nullable|string',
            'eventDate'            => 'required|date',
            'eventStart'           => 'required|string',
            'eventEnd'             => 'required|string',
            'startDateTime'        => 'required|date',
            'endDateTime'          => 'required|date|after:startDateTime',
            'hodlAsset'            => 'boolean',
            'policyIds'            => 'required|array|min:1',
            'nonceValidForMinutes' => 'required|integer|min:5',
            'image'                => 'nullable|image',
        ]);

        $data['event_id'] = $event->id;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('public');
        }

        try {
            $this->eventService->save($data);
            return redirect()
                ->route('admin.manage-events.index')
                ->with('status', __('Event updated'));
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => __('Failed to save event')]);
        }
    }

    public function destroy(Event $event): RedirectResponse
    {
        try {
            $this->eventService->delete($event->id);
            return redirect()
                ->route('admin.manage-events.index')
                ->with('status', __('Event deleted'));
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => __('Failed to delete event')]);
        }
    }
}
