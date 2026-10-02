<?php

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Controller;
use App\Http\Resources\HospitalizedPatientResource;
use App\Models\Patient;
use App\Models\Room;
use App\Models\Stay;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WarehouseActivePatientController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $stays = Stay::query()
            ->active()
            ->with([
                'patient:id,name,last_name_one,last_name_two',
                'room:id,number',
            ])
            ->whereHas('patient')
            ->whereHas('room')
            ->orderBy(
                Room::query()
                    ->select('number')
                    ->whereColumn('rooms.id', 'stays.room_id')
            )
            ->orderBy(
                Patient::query()
                    ->select('name')
                    ->whereColumn('patients.id', 'stays.patient_id')
            )
            ->orderBy('stays.id')
            ->get();

        return HospitalizedPatientResource::collection($stays);
    }
}
