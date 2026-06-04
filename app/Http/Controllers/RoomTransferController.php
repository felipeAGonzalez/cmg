<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomTransferRequest;
use App\Models\Room;
use App\Models\Stay;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomTransferController extends Controller
{
    public function create(Stay $stay): View|RedirectResponse
    {
        if (! $stay->isActive()) {
            return redirect()->route('rooms.index')
                ->with('error', 'No se puede trasladar una estancia que ya fue dada de alta.');
        }

        $availableRooms = Room::with('currentStay')
            ->orderBy('number')
            ->get()
            ->filter(fn(Room $r) => $r->isAvailable() && $r->id !== $stay->room_id)
            ->values();

        $stay->load(['patient', 'room']);

        return view('room-transfers.create', compact('stay', 'availableRooms'));
    }

    public function store(StoreRoomTransferRequest $request, Stay $stay): RedirectResponse
    {
        if (! $stay->isActive()) {
            return redirect()->route('rooms.index')
                ->with('error', 'No se puede trasladar una estancia finalizada.');
        }

        $toRoomId = $request->to_room_id;

        if ($toRoomId == $stay->room_id) {
            return back()->with('error', 'El cuarto de destino debe ser diferente al actual.');
        }

        $toRoom = Room::with('currentStay')->find($toRoomId);

        if (! $toRoom || ! $toRoom->isAvailable()) {
            return back()->with('error', 'El cuarto de destino no está disponible.');
        }

        $stay->roomTransfers()->create([
            'from_room_id'   => $stay->room_id,
            'to_room_id'     => $toRoomId,
            'transferred_at' => now(),
            'transferred_by' => auth()->id(),
        ]);

        $stay->update(['room_id' => $toRoomId]);

        return redirect()->route('stays.show', $toRoom)
            ->with('success', 'Paciente trasladado correctamente al Cuarto ' . $toRoom->number . '.');
    }
}
