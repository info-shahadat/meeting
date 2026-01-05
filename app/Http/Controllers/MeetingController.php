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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email'
        ]);

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

    public function index()
    {
        return view('meeting.meet-list');
    }

    public function meetListData()
    {
        $meetings = Meeting::with('host')->get();

        $meetings = $meetings->map(function ($meeting) {
            return [
                'title'     => $meeting->title,
                'room'      => $meeting->room,
                'creator'   => $meeting->host ? $meeting->host->name : 'Unknown',
                'created_at'=> $meeting->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json(['data' => $meetings]);
    }

    public function update(Request $request, $room)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $meeting = Meeting::where('room', $room)->firstOrFail();

        if ($meeting->host_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $meeting->title = $request->title;
        $meeting->save();

        return response()->json(['success' => true, 'title' => $meeting->title]);
    }

    public function destroy($room)
    {
        $meeting = Meeting::where('room', $room)->firstOrFail();
        $meeting->delete();
        return redirect()->route('meet.list')->with('success', 'Meeting deleted successfully');
    }

}

