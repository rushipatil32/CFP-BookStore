<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function test_SuccessfulRegister()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/register', [
            "role" => "user",
            "firstname" => "nayan",
            "lastname" => "patil",
            "email" => "nayan@gmail.com",
            "phone_no" => "1236549878",
            "password" => "pass@123",
            "confirm_password" => "pass@123"
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'User Successfully Registerd']);
    }

    public function test_UnsuccessfulRegister()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/register', [
            "role" => "user",
            "firstname" => "jayesh",
            "lastname" => "patil",
            "email" => "kiran@gmail.com",
            "phone_no" => "7624565498",
            "password" => "pass@123",
            "confirm_password" => "pass@123"
        ]);
        $response->assertStatus(200)->assertJson(['message' => 'The email has already been taken']);
    }

     /**
     * @test for
     * Successfull Login
     */
    public function test_SuccessfulLogin()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json(
            'POST',
            '/api/login',
            [
                "email" => "kiran@gmail.com",
                "password" => "pass@123"
            ]
        );
        $response->assertStatus(200)->assertJson(['message' => 'Login successfull']);
    }
    /**
     * @test for
     * Unsuccessfull Login
     */
    public function test_UnSuccessfulLogin()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json(
            'POST',
            '/api/login',
            [
                "email" => "kiran1997@gmail.com",
                "password" => "pass@123"
            ]
        );
        $response->assertStatus(401)->assertJson(['message' => 'email not found register first']);
    }

    /**
     * @test for
     * Successfull Logout
     */
    public function test_SuccessfulLogout()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MTg3NDkxNSwiZXhwIjoxNjQxODc4NTE1LCJuYmYiOjE2NDE4NzQ5MTUsImp0aSI6Im9iQ3FQVUJNRDJqWjU3RlgiLCJzdWIiOjEzLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.prg4TCsRpkLMXTCI1yEqFy9GTvp99lrBy0AgRKQiKVY'
        ])->json('POST', '/api/logout');
        $response->assertStatus(201)->assertJson(['message' => 'User successfully signed out']);
    }

    public function test_SuccessfulForgotPassword()
    { 
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/forgotPassword', [
                "email" => "kiran@gmail.com.com"
            ]);

            $response->assertStatus(200)->assertJson(['message' => 'password reset link genereted in mail']);
        
    }
    /**
     * @test for
     * UnSuccessfull forgotpassword
     */
    public function test_UnSuccessfulForgotPassword()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/forgotpassword', [
                "email" => "kiran1998@gmail.com"
            ]);

            $response->assertStatus(404)->assertJson(['message' => 'can not find a user with this email address']);
        }
    }
    /**
     * @test for
     * Successfull resetpassword
     */
    public function test_SuccessfulResetPassword()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
                'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MTg3NDkxNSwiZXhwIjoxNjQxODc4NTE1LCJuYmYiOjE2NDE4NzQ5MTUsImp0aSI6Im9iQ3FQVUJNRDJqWjU3RlgiLCJzdWIiOjEzLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.prg4TCsRpkLMXTCI1yEqFy9GTvp99lrBy0AgRKQiKVY'
            ])->json('POST', '/api/resetpassword', [
                "new_password" => "kiran@123",
                "confirm_password" => "kiran@123"
            ]);

            $response->assertStatus(201)->assertJson(['message' => 'Password reset successfull!']);
        }
    }
    /**
     * @test for
     * UnSuccessfull resetpassword
     */
    public function test_UnSuccessfulResetPassword()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
                'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MTg3NDkxNSwiZXhwIjoxNjQxODc4NTE1LCJuYmYiOjE2NDE4NzQ5MTUsImp0aSI6Im9iQ3FQVUJNRDJqWjU3RlgiLCJzdWIiOjEzLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.prg4TCsRpkLMXTCI1yEqFy9GTvp99lrBy0AgRKQiKVY'
            ])->json('POST', '/api/resetpassword', [
                "new_password" => "kiran@123",
                "confirm_password" => "kiran@123"
            ]);

            $response->assertStatus(400)->assertJson(['message' => 'can not find the user with that e-mail address']);
        }
    }
}
