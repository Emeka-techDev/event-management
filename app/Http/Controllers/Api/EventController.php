<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        // dd("I got here");
        $query = Event::query();
        // $this->shouldIncludeRelation('user');
        $relations = ['user', 'attendees', 'attendees.user'];

        foreach ($relations as $relation) {
            $query->when(
                $this->shouldIncludeRelation($relation),
                fn($q) => $q->with($relation)
            );
        }
        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $event = Event::create([
            ...$request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time'
            ]),
            'user_id' => 1
        ]);

        return new EventResource($event);
    }

    protected function shouldIncludeRelation(string $relation): bool
    {
        
        $include = request()->query('include');

        if (!$include) {
            return false;
        }

        // $relations = array_map('trim', explode(',', $include));
        $relations = explode(',', $include);

        // dd($relations);
        return in_array($relation, $relations);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        //
        $event->load('user', 'attendees');
       return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        //
        $event->update(
            $request->validate([
                'name' => 'sometimes|string',
                'description' => 'sometimes|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time'
            ]),
        );

        return new EventResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        //
        $event->delete();

        return response(status: 204);

    }
}
