<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;
use App\Models\{User};
use Str;

class AuthTest extends TestCase
{
    //this refreshdatabase clears all the database when the test is running (so that it wont mess with the data)
    // use RefreshDatabase; 
    /**
     * A basic feature test example.
     */
    // public function test_example(): void
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    public function test_user_can_register(): void
    {
        $email = "testuser@example.com";
    
        // Assert that the email does NOT exist before registration
        $this->assertDatabaseMissing('users', ['email' => $email]);
    
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => $email,
            'role' => 'client',
            'password' => 'admin',
            'verification_token' => hash_hmac('sha256', Str::random(40), env('APP_KEY'))
        ]);
    
        // Assert the user was created successfully
        $response->assertStatus(201);
        
        // Assert the email now EXISTS in the database after registration
        $this->assertDatabaseHas('users', ['email' => $email]);

        /*difference between $this->assert and $response->assert 
            > $this is for general assertions (database values, variables etc)
            > $response is for http response assertions such as status codes, message (assertJson) , etc...  
        */  
    }

    public function test_user_can_login(): void
    {
        $response = $this->postJson('/api/auth/login',[
            'email' => 'admin@gmail.com',
            'password' => 'admin'
        ]);

        $response->assertStatus(200);
    }

    public function test_user_cant_login(): void
    {
        $response = $this->postJson('/api/auth/login',[
            'email' => 'admin@gmail.com',
            'password' => 'admins'
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_retrieve_jwt_cookie(): void
    {
        $response = $this->postJson('/api/auth/login',[
            'email' => 'admin@gmail.com',
            'password' => 'admin'
        ]);
        $cookie = Cookie::get('auth_token');
        $response->assertCookie('auth_token',$cookie);
    }







}
