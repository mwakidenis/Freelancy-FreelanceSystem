<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FreelanceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_user_can_create_freelance_project(): void
    {
        $response = $this->postJson('/api/auth/login',[
            'email' => 'sampleclient@gmail.com',
            'password' => 'admin'
        ]);
        $cookie = $response->headers->getCookies();
        $response = $this->call('POST','/api/freelances',[
            'title' => 'LATEST UPDATED TestER Project',
            'description' => 'LATEST UPDATED Test Project is for testing',
            'category' => 'testing',
            'rate' => 500
        ],$cookie);
        $response->assertStatus(201);
    }

    public function test_user_cant_create_freelance_project(): void
    {
        $response = $this->postJson('/api/auth/login',[
            'email' => 'admin@gmail.com',
            'password' => 'admin'
        ]);
        $cookie = $response->headers->getCookies();
        $response = $this->call('POST','/api/freelances',[
            'title' => 'LATEST UPDATED TestER Project',
            'description' => 'LATEST UPDATED Test Project is for testing',
            'category' => 'testing',
            'rate' => 500
        ],$cookie);
        $response->assertStatus(401);
    }
}
