<?php

namespace App\Http\Controllers;

use App\Models\Stay;
use App\Models\StayInstruction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $user  = auth()->user();
        $tab    = $request->query('tab', 'active');
        $search = trim($request->query('search', ''));

        $query = Stay::query()->with(['patient', 'room', 'currentDoctors.doctor']);

        // Scope by role
        if ($user->isDoctor()) {
            // All stays where this doctor is or was ever assigned
            $query->whereHas('stayDoctors', fn($q) => $q->where('doctor_id', $user->id));
        }
        // Admin and nurse see all stays (no extra filter)

        // Tab filter
        if ($tab === 'active') {
            $query->whereNull('discharge_date');
        } else {
            $query->whereNotNull('discharge_date');
        }

        // Name search across patient's 3 name columns
        if ($search !== '') {
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('last_name_one', 'like', '%' . $search . '%')
                  ->orWhere('last_name_two', 'like', '%' . $search . '%');
            });
        }

        $query->when($tab === 'active',
            fn($q) => $q->orderByDesc('admission_date'),
            fn($q) => $q->orderByDesc('discharge_date')
        );

        $stays = $query->get();

        return view('doctor.my-patients', [
            'stays'           => $stays,
            'tab'             => $tab,
            'search'          => $search,
            'activeCount'     => $this->countStays($user, 'active'),
            'dischargedCount' => $this->countStays($user, 'discharged'),
            'doctor'          => $user,
        ]);
    }

    public function show(Stay $stay): View|RedirectResponse
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            if ($user->isDoctor()) {
                // Allow if the doctor was ever assigned (including historical)
                $wasAssigned = $stay->stayDoctors()->where('doctor_id', $user->id)->exists();
                if (!$wasAssigned) abort(403, 'No tienes acceso a este paciente.');
            }
            // Nurses can see all stays
        }

        $stay->load([
            'patient',
            'room',
            'currentDoctors.doctor.specialties',
            'stayDoctors.doctor',
            'roomTransfers.fromRoom',
            'roomTransfers.toRoom',
            'roomTransfers.transferredBy',
            'instructions.doctor',
            'stayDocuments.document',
            'medicationOrders.prescribedBy',
            'dischargeIndicatedBy',
        ]);

        $doctor       = $user->isDoctor() ? $user : null;
        $myAssignment = $doctor
            ? $stay->currentDoctors->where('doctor_id', $doctor->id)->first()
            : null;

        return view('doctor.patient-detail', compact('stay', 'doctor', 'myAssignment'));
    }

    public function storeInstruction(Request $request, Stay $stay): RedirectResponse
    {
        $doctor = auth()->user();

        $isAssigned = $stay->currentDoctors()->where('doctor_id', $doctor->id)->exists();

        if (! $isAssigned) {
            abort(403, 'Solo puedes escribir indicaciones para pacientes asignados a ti.');
        }

        $request->validate([
            'body' => 'required|string|max:3000',
        ], [
            'body.required' => 'La indicación no puede estar vacía.',
            'body.max'      => 'La indicación no puede superar 3,000 caracteres.',
        ]);

        StayInstruction::create([
            'stay_id'   => $stay->id,
            'doctor_id' => $doctor->id,
            'body'      => $request->body,
        ]);

        return back()->with('success', 'Indicación registrada correctamente.');
    }

    protected function countStays(User $user, string $type): int
    {
        $query = Stay::query();

        if ($user->isDoctor()) {
            $query->whereHas('stayDoctors', fn($q) => $q->where('doctor_id', $user->id));
        }

        if ($type === 'active') {
            $query->whereNull('discharge_date');
        } else {
            $query->whereNotNull('discharge_date');
        }

        return $query->count();
    }
}
