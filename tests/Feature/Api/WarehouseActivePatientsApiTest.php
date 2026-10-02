<?php

namespace Tests\Feature\Api;

use App\Models\Patient;
use App\Models\Room;
use App\Models\Stay;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WarehouseActivePatientsApiTest extends TestCase
{
    private const ENDPOINT = '/api/integrations/warehouse/active-patients';

    private string $validToken = 'warehouse-test-token';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createIntegrationSchema();

        config([
            'integrations.warehouse.api_token_hash' => hash('sha256', $this->validToken),
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('stays');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('rooms');

        parent::tearDown();
    }

    private function createIntegrationSchema(): void
    {
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

        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->integer('number')->unique();
            $table->timestamps();
        });

        Schema::create('stays', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('birth_parent_stay_id')
                ->nullable()
                ->constrained('stays')
                ->nullOnDelete();
            $table->text('diagnosis');
            $table->datetime('admission_date');
            $table->datetime('discharge_date')->nullable();
            $table->string('discharge_reason')->nullable();
            $table->timestamp('discharge_indicated_at')->nullable();
            $table->foreignId('discharge_indicated_by_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_request_without_bearer_token_is_rejected(): void
    {
        $this->getJson(self::ENDPOINT)
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'No autorizado.']);
    }

    public function test_request_with_invalid_bearer_token_is_rejected(): void
    {
        $this->withToken('invalid-token')
            ->getJson(self::ENDPOINT)
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'No autorizado.']);
    }

    public function test_request_with_valid_bearer_token_succeeds_without_web_session(): void
    {
        $this->assertGuest();

        $this->authorizedGet()
            ->assertOk()
            ->assertExactJson(['data' => []]);

        $this->assertGuest();
    }

    public function test_response_does_not_expose_token_or_configured_hash(): void
    {
        $response = $this->authorizedGet()->assertOk();
        $content = $response->getContent();

        $this->assertStringNotContainsString($this->validToken, $content);
        $this->assertStringNotContainsString(hash('sha256', $this->validToken), $content);
    }

    public function test_missing_server_configuration_returns_service_unavailable(): void
    {
        config(['integrations.warehouse.api_token_hash' => null]);

        $this->withToken($this->validToken)
            ->getJson(self::ENDPOINT)
            ->assertStatus(503)
            ->assertExactJson([
                'message' => 'Servicio temporalmente no disponible.',
            ]);
    }

    public function test_active_stay_is_returned_with_exact_contract(): void
    {
        $room = $this->createRoom(204);
        $patient = $this->createPatient('Juan', 'Pérez', 'López');
        $stay = $this->createStay($patient, $room);

        $this->authorizedGet()
            ->assertOk()
            ->assertExactJson([
                'data' => [[
                    'patient_id' => (string) $patient->id,
                    'hospitalization_id' => (string) $stay->id,
                    'patient_name' => $patient->fullName(),
                    'room_id' => (string) $room->id,
                    'room_number' => (string) $room->number,
                ]],
            ]);
    }

    public function test_discharged_stay_is_not_returned(): void
    {
        $room = $this->createRoom(204);
        $patient = $this->createPatient();
        $this->createStay($patient, $room, [
            'discharge_date' => now(),
        ]);

        $this->authorizedGet()
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_stay_with_discharge_indicated_remains_active(): void
    {
        $room = $this->createRoom(204);
        $patient = $this->createPatient();
        $stay = $this->createStay($patient, $room, [
            'discharge_indicated_at' => now(),
        ]);

        $this->authorizedGet()
            ->assertOk()
            ->assertJsonPath('data.0.hospitalization_id', (string) $stay->id);
    }

    public function test_multiple_active_stays_are_returned(): void
    {
        $firstStay = $this->createStay(
            $this->createPatient('Ana', 'García'),
            $this->createRoom(101)
        );
        $secondStay = $this->createStay(
            $this->createPatient('Beatriz', 'Mora'),
            $this->createRoom(102)
        );

        $response = $this->authorizedGet()->assertOk();

        $response->assertJsonCount(2, 'data');
        $this->assertEqualsCanonicalizing(
            [(string) $firstStay->id, (string) $secondStay->id],
            collect($response->json('data'))->pluck('hospitalization_id')->all()
        );
    }

    public function test_empty_response_returns_data_array(): void
    {
        $this->authorizedGet()
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_patient_name_uses_official_full_name_method(): void
    {
        $patient = $this->createPatient('María Elena', 'Sánchez', 'Díaz');
        $this->createStay($patient, $this->createRoom(301));

        $this->authorizedGet()
            ->assertOk()
            ->assertJsonPath('data.0.patient_name', $patient->fullName());
    }

    public function test_hospitalization_id_is_stay_id_and_not_patient_id(): void
    {
        $room = $this->createRoom(204);
        $this->createPatient('Unused', 'Patient');
        $patient = $this->createPatient('Active', 'Patient');
        $stay = $this->createStay($patient, $room);

        $this->assertNotSame($patient->id, $stay->id);

        $this->authorizedGet()
            ->assertJsonPath('data.0.patient_id', (string) $patient->id)
            ->assertJsonPath('data.0.hospitalization_id', (string) $stay->id);
    }

    public function test_mother_and_newborn_in_same_room_are_not_deduplicated(): void
    {
        $room = $this->createRoom(204);
        $motherStay = $this->createStay(
            $this->createPatient('Madre', 'Paciente'),
            $room
        );
        $newbornStay = $this->createStay(
            $this->createPatient('Bebé', 'Paciente'),
            $room,
            ['birth_parent_stay_id' => $motherStay->id]
        );

        $response = $this->authorizedGet()->assertOk();

        $response->assertJsonCount(2, 'data');

        $data = collect($response->json('data'));
        $this->assertSame(
            [(string) $room->id],
            $data->pluck('room_id')->unique()->values()->all()
        );
        $this->assertEqualsCanonicalizing(
            [(string) $motherStay->id, (string) $newbornStay->id],
            $data->pluck('hospitalization_id')->all()
        );
    }

    public function test_current_stay_room_is_returned_after_transfer(): void
    {
        $roomA = $this->createRoom(101);
        $roomB = $this->createRoom(202);
        $stay = $this->createStay($this->createPatient(), $roomA);

        $stay->update(['room_id' => $roomB->id]);

        $this->authorizedGet()
            ->assertOk()
            ->assertJsonPath('data.0.room_id', (string) $roomB->id)
            ->assertJsonPath('data.0.room_number', (string) $roomB->number)
            ->assertJsonMissing([
                'room_id' => (string) $roomA->id,
                'room_number' => (string) $roomA->number,
            ]);
    }

    public function test_response_excludes_clinical_and_demographic_fields(): void
    {
        $stay = $this->createStay(
            $this->createPatient(),
            $this->createRoom(204)
        );

        $item = $this->authorizedGet()
            ->assertOk()
            ->json('data.0');

        $this->assertSame([
            'patient_id',
            'hospitalization_id',
            'patient_name',
            'room_id',
            'room_number',
        ], array_keys($item));

        foreach ([
            'birth_date',
            'gender',
            'diagnosis',
            'discharge_reason',
            'admission_date',
            'discharge_date',
        ] as $privateField) {
            $this->assertArrayNotHasKey($privateField, $item);
        }

        $this->assertSame((string) $stay->id, $item['hospitalization_id']);
    }

    private function authorizedGet()
    {
        return $this->withToken($this->validToken)->getJson(self::ENDPOINT);
    }

    private function createPatient(
        string $name = 'Paciente',
        string $lastNameOne = 'Prueba',
        ?string $lastNameTwo = null
    ): Patient {
        return Patient::create([
            'name' => $name,
            'last_name_one' => $lastNameOne,
            'last_name_two' => $lastNameTwo,
            'birth_date' => '1990-01-01',
            'gender' => 'F',
        ]);
    }

    private function createRoom(int $number): Room
    {
        return Room::create(['number' => $number]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createStay(
        Patient $patient,
        Room $room,
        array $attributes = []
    ): Stay {
        return Stay::create(array_merge([
            'patient_id' => $patient->id,
            'room_id' => $room->id,
            'diagnosis' => 'Diagnóstico privado de prueba',
            'admission_date' => now(),
        ], $attributes));
    }
}
