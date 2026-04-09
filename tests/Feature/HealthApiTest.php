<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthApiTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'timestamp',
            ])
            ->assertJson([
                'status' => 'ok',
                'message' => 'API is ready for the new project.',
            ]);
    }
}
