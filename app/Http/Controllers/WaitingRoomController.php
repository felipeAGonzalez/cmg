<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\TriageRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaitingRoomController extends Controller
{
    public function index(): View
    {
        $triages = TriageRecord::with(['patient', 'performedBy'])
            ->pending()
            ->orderedForWaitingRoom()
            ->get();

        return view('waiting-room.index', compact('triages'));
    }

    public function start(): View
    {
        return view('waiting-room.start');
    }

    public function searchPatients(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $patients = Patient::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('last_name_one', 'like', "%{$query}%")
                  ->orWhere('last_name_two', 'like', "%{$query}%")
                  ->orWhereRaw(
                      "CONCAT_WS(' ', name, last_name_one, last_name_two) LIKE ?",
                      ["%{$query}%"]
                  );

                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $query)) {
                    $q->orWhere('birth_date', $query);
                } elseif (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $query, $m)) {
                    $q->orWhere('birth_date', "{$m[3]}-{$m[2]}-{$m[1]}");
                }
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'last_name_one', 'last_name_two', 'birth_date', 'gender']);

        $results = $patients->map(fn ($p) => [
            'id' => $p->id,
            'full_name' => $p->fullName(),
            'birth_date' => $p->birth_date?->format('d/m/Y'),
            'age' => $p->birth_date?->age,
            'gender' => $p->gender,
        ]);

        return response()->json(['results' => $results]);
    }
}
