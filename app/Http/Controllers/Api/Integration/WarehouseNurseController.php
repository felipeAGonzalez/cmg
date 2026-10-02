<?php

namespace App\Http\Controllers\Api\Integration;

use App\Http\Controllers\Controller;
use App\Http\Resources\HospitalNurseResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WarehouseNurseController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $nurses = User::query()
            ->select(['id', 'name', 'last_name_one', 'last_name_two', 'email'])
            ->where('role', 'nurse')
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return HospitalNurseResource::collection($nurses);
    }
}
