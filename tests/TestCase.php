<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use DatabaseSeeder;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    public function setUp(): void
    {
        parent::setUp();
        //$this->withoutExceptionHandling();
        (new DatabaseSeeder())->call(DatabaseSeeder::class);
        
    }
    
    protected function signIn($user = null) //student
    {
        
        $credentials = [
            'email' => 'student@curriculumonline.de',
            'password' => 'password',
        ];
        $this->followingRedirects()->post('login', $credentials)->assertStatus(200);
        $this->actingAs(auth()->user());

        return auth()->user();
    }
    
    protected function signInAdmin($user = null)
    {
        $credentials = [
            'email' => 'admin@curriculumonline.de',
            'password' => 'password',
        ];
        $this->followingRedirects()->post('login', $credentials)->assertStatus(200);
        $this->actingAs(auth()->user());
       
        
        return auth()->user();
    }
    
    protected function signInStudent($user = null)
    {
        $credentials = [
            'email' => 'student@curriculumonline.de',
            'password' => 'password',
        ];
        $this->followingRedirects()->post('login', $credentials)->assertStatus(200);
        $this->actingAs(auth()->user());
       
        
        return auth()->user();
    }
    
    protected function signInApiAdmin($user = null)
    {
        $this->withoutExceptionHandling();
        $credentials = [
            'email' => 'admin@curriculumonline.de',
            'password' => 'password',
        ];
        $this->followingRedirects()->post('login', $credentials)->assertStatus(200);
        return Passport::actingAsClient(auth()->user());
        
    }   
}
