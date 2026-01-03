<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\MeetingParticipant;

class MeetingController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $meeting = Meeting::create([
            'title'   => $request->title ?? 'New Meeting',
            'room'    => Meeting::generateRoom(),
            'host_id' => auth()->id(),
        ]);

        return redirect()->route('meet.join', ['room' => $meeting->room]);
    }

    public function join($room)
    {
        $meeting = Meeting::where('room', $room)->firstOrFail();

        return view('meeting.join', compact('meeting'));
    }

    public function recordJoin(Request $request, $room)
    {
        $meeting = Meeting::where('room', $room)->firstOrFail();

        $participant = MeetingParticipant::create([
            'meeting_id' => $meeting->id,
            'name'       => $request->name,
            'email'      => $request->email,
            'joined_at'  => now(),
        ]);

        return response()->json(['id' => $participant->id]);
    }

    public function recordLeave(Request $request, $room)
    {
        MeetingParticipant::where('id', $request->id)->update([
            'left_at' => now()
        ]);
    }
}

