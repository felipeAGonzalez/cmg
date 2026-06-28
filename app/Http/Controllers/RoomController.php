<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use App\Models\Stay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $rooms = Room::with(['currentStays.patient', 'currentStays.dischargeIndicatedBy'])
            ->orderBy('number')
            ->get();

        $total     = $rooms->count();
        $available = $rooms->filter(fn($r) => $r->isAvailable())->count();
        $occupied  = $total - $available;

        $search       = $request->query('search');
        $filter       = $request->query('filter', 'all');
        $displayRooms = $rooms;

        if ($search) {
            $displayRooms = $rooms->filter(function (Room $room) use ($search) {
                if (is_numeric($search) && $room->number == (int) $search) {
                    return true;
                }

                return $room->currentStays->contains(function ($stay) use ($search) {
                    return $stay->patient && str_contains(
                        strtolower($stay->patient->fullName()),
                        strtolower($search)
                    );
                });
            });
        }

        if ($filter === 'discharge_pending') {
            $displayRooms = $displayRooms->filter(
                fn(Room $room) => $room->currentStays->contains(
                    fn($stay) => $stay->hasDischargeIndicated()
                )
            );
        }

        $dischargePendingCount = Stay::withDischargeIndicated()->count();

        return view('rooms.index', compact(
            'displayRooms', 'total', 'available', 'occupied',
            'search', 'filter', 'dischargePendingCount'
        ));
    }

    public function create(): View
    {
        return view('rooms.create');
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        Room::create($request->validated());

        return redirect()->route('rooms.index')
            ->with('success', 'Cuarto ' . $request->number . ' agregado correctamente.');
    }

    public function edit(Room $room): View
    {
        return view('rooms.edit', compact('room'));
    }

    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $room->update($request->validated());

        return redirect()->route('rooms.index')
            ->with('success', 'Cuarto actualizado correctamente.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if (! $room->isAvailable()) {
            return redirect()->route('rooms.index')
                ->with('error', 'No se puede eliminar el cuarto ' . $room->number . ' porque tiene una estancia activa.');
        }

        $room->delete();

        return redirect()->route('rooms.index')
            ->with('success', 'Cuarto ' . $room->number . ' eliminado correctamente.');
    }
}
