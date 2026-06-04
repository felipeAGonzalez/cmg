<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 11) as $number) {
            Room::updateOrCreate(['number' => $number]);
        }
    }
}
