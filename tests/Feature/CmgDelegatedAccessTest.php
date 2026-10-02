<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Room;
use App\Models\Stay;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CmgDelegatedAccessTest extends TestCase
{
    private const SECRET = 'cmg-feature-delegated-secret';

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
        config([
            'cmg.url' => 'https://warehouse.example.test/base',
            'cmg.delegated_auth.secret' => self::SECRET,
            'cmg.delegated_auth.ttl' => 60,
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('stays');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    public function test_nurse_can_emit_for_active_stay_and_redirect_to_configured_cmg(): void
    {
        $nurse = $this->user('nurse');
        $stay = $this->stay();

        $response = $this->actingAs($nurse)->post(route('stays.warehouse-access', $stay));

        $response->assertRedirectContains('https://warehouse.example.test/base/auth/hospital/delegated?token=')
            ->assertHeader('Referrer-Policy', 'no-referrer');
        $payload = $this->redirectPayload($response->headers->get('Location'));
        $this->assertSame((string) $nurse->id, $payload['hospital_user_id']);
        $this->assertSame((string) $stay->patient_id, $payload['patient_id']);
        $this->assertSame((string) $stay->id, $payload['hospitalization_id']);
        $this->assertSame((string) $stay->room_id, $payload['room_id']);
        $this->assertSame((string) $stay->room->number, $payload['room_number']);
    }

    public function test_doctor_admin_and_root_receive_forbidden(): void
    {
        $stay = $this->stay();
        foreach (['doctor', 'admin', 'root'] as $role) {
            $this->actingAs($this->user($role))->post(route('stays.warehouse-access', $stay))->assertForbidden();
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->post(route('stays.warehouse-access', $this->stay()))->assertRedirect(route('login'));
    }

    public function test_discharged_stay_does_not_emit(): void
    {
        $stay = $this->stay(['discharge_date' => now()]);

        $this->actingAs($this->user('nurse'))->from('/rooms/1/patient')
            ->post(route('stays.warehouse-access', $stay))
            ->assertRedirect('/rooms/1/patient')
            ->assertSessionHas('error', 'La hospitalización ya no se encuentra activa.');
    }

    public function test_current_room_is_used_and_request_data_cannot_replace_context(): void
    {
        $stay = $this->stay();
        $otherRoom = Room::create(['number' => 999]);
        $otherPatient = $this->patient('Manipulated');

        $response = $this->actingAs($this->user('nurse'))->post(route('stays.warehouse-access', $stay), [
            'patient_id' => $otherPatient->id,
            'hospitalization_id' => 999999,
            'room_id' => $otherRoom->id,
            'hospital_user_id' => 999999,
        ]);

        $payload = $this->redirectPayload($response->headers->get('Location'));
        $this->assertSame((string) $stay->patient_id, $payload['patient_id']);
        $this->assertSame((string) $stay->id, $payload['hospitalization_id']);
        $this->assertSame((string) $stay->room_id, $payload['room_id']);
        $this->assertSame((string) $stay->room->number, $payload['room_number']);
    }

    public function test_mother_and_newborn_in_same_room_receive_distinct_tokens(): void
    {
        $room = Room::create(['number' => 204]);
        $mother = $this->stay([], $room, $this->patient('Mother'));
        $newborn = $this->stay(['birth_parent_stay_id' => $mother->id], $room, $this->patient('Newborn'));
        $nurse = $this->user('nurse');

        $first = $this->actingAs($nurse)->post(route('stays.warehouse-access', $mother))->headers->get('Location');
        $second = $this->actingAs($nurse)->post(route('stays.warehouse-access', $newborn))->headers->get('Location');

        $this->assertNotSame($first, $second);
        $this->assertSame((string) $mother->id, $this->redirectPayload($first)['hospitalization_id']);
        $this->assertSame((string) $newborn->id, $this->redirectPayload($second)['hospitalization_id']);
    }

    public function test_missing_url_or_secret_fails_safely(): void
    {
        $nurse = $this->user('nurse');
        $stay = $this->stay();

        config(['cmg.url' => '']);
        $this->actingAs($nurse)->from('/rooms/1/patient')->post(route('stays.warehouse-access', $stay))
            ->assertRedirect('/rooms/1/patient')->assertSessionHas('error', 'La integración con Almacén no está disponible en este momento.');

        config(['cmg.url' => 'https://warehouse.example.test', 'cmg.delegated_auth.secret' => '']);
        $this->actingAs($nurse)->from('/rooms/1/patient')->post(route('stays.warehouse-access', $stay))
            ->assertRedirect('/rooms/1/patient')->assertSessionHas('error', 'La integración con Almacén no está disponible en este momento.');
    }

    public function test_button_is_only_visible_to_nurse_for_active_stay(): void
    {
        $active = $this->stay();
        $discharged = clone $active;
        $discharged->discharge_date = now();

        $nurseHtml = Blade::render("@include('stays._warehouse_access')", ['user' => $this->user('nurse'), 'stay' => $active]);
        $this->assertStringContainsString('Solicitar a Almacén', $nurseHtml);
        $this->assertStringContainsString('method="POST"', $nurseHtml);
        $this->assertStringNotContainsString('Solicitar a Almacén', Blade::render("@include('stays._warehouse_access')", ['user' => $this->user('doctor'), 'stay' => $active]));
        $this->assertStringNotContainsString('Solicitar a Almacén', Blade::render("@include('stays._warehouse_access')", ['user' => $this->user('admin'), 'stay' => $active]));
        $this->assertStringNotContainsString('Solicitar a Almacén', Blade::render("@include('stays._warehouse_access')", ['user' => $this->user('nurse'), 'stay' => $discharged]));
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('last_name_one');
            $table->string('last_name_two')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->boolean('must_change_password')->default(false);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->integer('number')->unique();
            $table->timestamps();
        });
        Schema::create('patients', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('last_name_one');
            $table->string('last_name_two')->nullable();
            $table->date('birth_date');
            $table->char('gender', 1);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('stays', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('birth_parent_stay_id')->nullable()->constrained('stays')->nullOnDelete();
            $table->text('diagnosis');
            $table->datetime('admission_date');
            $table->datetime('discharge_date')->nullable();
            $table->timestamps();
        });
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'last_name_one' => 'Tester',
            'email' => $role.'-'.fake()->uuid().'@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }

    private function patient(string $name = 'Patient'): Patient
    {
        return Patient::create([
            'name' => $name,
            'last_name_one' => 'Tester',
            'birth_date' => '1990-01-01',
            'gender' => 'F',
        ]);
    }

    private function stay(array $overrides = [], ?Room $room = null, ?Patient $patient = null): Stay
    {
        $room ??= Room::create(['number' => fake()->unique()->numberBetween(100, 900)]);
        $patient ??= $this->patient();

        return Stay::create(array_merge([
            'patient_id' => $patient->id,
            'room_id' => $room->id,
            'diagnosis' => 'Observation',
            'admission_date' => now(),
            'discharge_date' => null,
        ], $overrides));
    }

    private function redirectPayload(string $location): array
    {
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $encodedPayload = explode('.', $query['token'])[0];
        $padding = (4 - strlen($encodedPayload) % 4) % 4;

        return json_decode(base64_decode(strtr($encodedPayload, '-_', '+/').str_repeat('=', $padding), true), true, 32, JSON_THROW_ON_ERROR);
    }
}
