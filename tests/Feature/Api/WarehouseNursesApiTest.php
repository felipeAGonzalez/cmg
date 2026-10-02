<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WarehouseNursesApiTest extends TestCase
{
    private const ENDPOINT = '/api/integrations/warehouse/nurses';

    private string $validToken = 'warehouse-nurses-test-token';

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('last_name_one');
            $table->string('last_name_two')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        config([
            'integrations.warehouse.api_token_hash' => hash('sha256', $this->validToken),
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_valid_token_returns_only_nurses_with_exact_contract(): void
    {
        $nurse = $this->createUser('nurse', 'María', 'López', 'Díaz', 'maria@example.com');
        $this->createUser('doctor', 'Doctor', 'Uno', null, 'doctor@example.com');
        $this->createUser('admin', 'Admin', 'Uno', null, 'admin@example.com');
        $this->createUser('root', 'Root', 'Uno', null, 'root@example.com');

        $response = $this->authorizedGet()->assertOk()->assertExactJson([
            'data' => [[
                'user_id' => (string) $nurse->id,
                'name' => $nurse->fullName(),
                'email' => $nurse->email,
            ]],
        ]);

        $this->assertIsString($response->json('data.0.user_id'));
        $this->assertSame(['user_id', 'name', 'email'], array_keys($response->json('data.0')));
    }

    public function test_nurses_are_ordered_by_name_and_then_id(): void
    {
        $second = $this->createUser('nurse', 'Beatriz', 'Mora', null, 'b@example.com');
        $first = $this->createUser('nurse', 'Ana', 'García', null, 'a@example.com');

        $this->authorizedGet()
            ->assertOk()
            ->assertJsonPath('data.0.user_id', (string) $first->id)
            ->assertJsonPath('data.1.user_id', (string) $second->id);
    }

    public function test_empty_result_is_valid(): void
    {
        $this->authorizedGet()->assertOk()->assertExactJson(['data' => []]);
    }

    public function test_missing_token_is_rejected(): void
    {
        $this->getJson(self::ENDPOINT)
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'No autorizado.']);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $this->withToken('invalid-token')->getJson(self::ENDPOINT)
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'No autorizado.']);
    }

    public function test_missing_configuration_returns_service_unavailable(): void
    {
        config(['integrations.warehouse.api_token_hash' => null]);

        $this->withToken($this->validToken)->getJson(self::ENDPOINT)
            ->assertStatus(503)
            ->assertExactJson(['message' => 'Servicio temporalmente no disponible.']);
    }

    private function authorizedGet()
    {
        return $this->withToken($this->validToken)->getJson(self::ENDPOINT);
    }

    private function createUser(
        string $role,
        string $name,
        string $lastNameOne,
        ?string $lastNameTwo,
        string $email,
    ): User {
        return User::query()->create([
            'name' => $name,
            'last_name_one' => $lastNameOne,
            'last_name_two' => $lastNameTwo,
            'email' => $email,
            'password' => 'password',
            'role' => $role,
        ]);
    }
}
