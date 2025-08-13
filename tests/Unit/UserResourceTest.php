<?php

namespace Tests\Unit;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    public function test_to_array_returns_correct_structure_for_authenticated_user()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $resource = new UserResource($user);
        $result   = $resource->toArray($request);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('email_verified_at', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('email_verified', $result);

        $this->assertEquals($user->id, $result['id']);
        $this->assertEquals($user->name, $result['name']);
        $this->assertEquals($user->email, $result['email']);
        $this->assertTrue($result['email_verified']);
    }

    public function test_to_array_returns_correct_structure_for_unauthenticated_user()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () {});

        $resource = new UserResource($user);
        $result   = $resource->toArray($request);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('email_verified_at', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayNotHasKey('email_verified', $result);

        $this->assertEquals($user->id, $result['id']);
        $this->assertEquals($user->name, $result['name']);
        $this->assertEquals($user->email, $result['email']);
    }

    public function test_to_array_returns_correct_structure_for_different_user()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $differentUser = User::factory()->create();

        $request = Request::create('/test');
        $request->setUserResolver(function () use ($differentUser) {
            return $differentUser;
        });

        $resource = new UserResource($user);
        $result   = $resource->toArray($request);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('email_verified_at', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayNotHasKey('email_verified', $result);

        $this->assertEquals($user->id, $result['id']);
        $this->assertEquals($user->name, $result['name']);
        $this->assertEquals($user->email, $result['email']);
    }

    public function test_to_array_handles_null_email_verified_at()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $request = Request::create('/test');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $resource = new UserResource($user);
        $result   = $resource->toArray($request);

        $this->assertArrayHasKey('email_verified', $result);
        $this->assertFalse($result['email_verified']);
        $this->assertNull($result['email_verified_at']);
    }
}
