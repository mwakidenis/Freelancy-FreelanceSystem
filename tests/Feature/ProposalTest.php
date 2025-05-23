<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProposalTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_user_can_create_proposal(): void 
    {
        $response = $this->postJson('/api/auth/login',[
            'email' => 'samplefreelancer@gmailcom',
            'password' => 'admin'
        ]);
        $cookies = $response->headers->getCookies();
        $response = $this->call('POST','/api/proposals',[
            'freelance_id' => 1,
            'title' => 'Test Proposal',
            'description' => 'Test description',
            'rate' => 500
        ],$cookies);
        $response->assertStatus(201);
    }

    public function test_user_cant_create_proposal(): void
    {
        $response = $this->postJson('/api/auth/login',[
            'email' => 'admin@gmail.com',
            'password' => 'admin'
        ]);
        $cookies = $response->headers->getCookies();
        $response = $this->call('POST','/api/proposals',[
            'freelance_id' => 1,
            'title' => 'Test Proposal',
            'description' => 'Test description',
            'rate' => 500
        ],$cookies);
        $response->assertStatus(401);
    }

    public function test_user_cant_create_multiple_proposal_to_a_single_project(): void
    {
        $freelance_id = 1;
        $response = $this->postJson('/api/auth/login',[
            'email' => 'samplefreelancer@gmailcom',
            'password' => 'admin'
        ]);
        $cookies = $response->headers->getCookies();
        $response = $this->call('POST','/api/proposals',[
            'freelance_id' => 1,
            'title' => 'Test Proposal',
            'description' => 'Test description',
            'rate' => 500
        ],$cookies);
        $user = JWTAuth::setToken($cookies[0]->getValue())->authenticate();
        $this->assertDatabaseHas('proposals',['freelance_id'=>$freelance_id,'freelancer_id'=>$user->id]);
        $response->assertStatus(403);
    }
}
