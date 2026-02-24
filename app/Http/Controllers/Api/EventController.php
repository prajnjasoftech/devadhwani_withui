<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * List all events (optionally filtered by temple_id)
     */
    public function index(Request $request)
    {
        $query = Event::latest();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->per_page ?? 10;
        $events = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $events,
            'meta' => [
                'current_page' => $events->currentPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'last_page' => $events->lastPage(),
            ],
        ]);
    }

    /**
     * Create new event
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'event_name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:upcoming,ongoing,completed,cancelled',
        ]);

        // Default status if not provided
        if (! isset($validated['status'])) {
            $validated['status'] = 'upcoming';
        }

        $event = Event::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Event created successfully',
            'data' => $event,
        ]);
    }

    /**
     * Show event details
     */
    public function show($id)
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json(['status' => false, 'error' => 'Event not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $event]);
    }

    /**
     * Update event
     */
    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json(['status' => false, 'error' => 'Event not found'], 404);
        }

        $validated = $request->validate([
            'temple_id' => 'required|exists:temples,id',
            'event_name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:upcoming,ongoing,completed,cancelled',
        ]);

        $event->update($validated);
        $event->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Event updated successfully',
            'data' => $event,
        ]);
    }

    /**
     * Soft delete event
     */
    public function destroy($id)
    {
        $event = Event::find($id);

        if (! $event) {
            return response()->json(['status' => false, 'error' => 'Event not found'], 404);
        }

        $event->delete();

        return response()->json(['status' => true, 'message' => 'Event deleted successfully']);
    }

    /**
     * List all trashed events
     */
    public function trashed(Request $request)
    {
        $query = Event::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $trashed = $query->get();

        return response()->json(['status' => true, 'data' => $trashed]);
    }

    /**
     * Restore a soft deleted event
     */
    public function restore($id)
    {
        $event = Event::onlyTrashed()->find($id);

        if (! $event) {
            return response()->json(['status' => false, 'error' => 'Event not found in trash'], 404);
        }

        $event->restore();

        return response()->json(['status' => true, 'message' => 'Event restored successfully']);
    }

    /**
     * Force delete event permanently
     */
    public function forceDelete($id)
    {
        $event = Event::onlyTrashed()->find($id);

        if (! $event) {
            return response()->json(['status' => false, 'error' => 'Event not found'], 404);
        }

        $event->forceDelete();

        return response()->json(['status' => true, 'message' => 'Event permanently deleted']);
    }
}
