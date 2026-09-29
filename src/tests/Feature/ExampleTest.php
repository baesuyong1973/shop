<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_トップページが正常に表示される(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
