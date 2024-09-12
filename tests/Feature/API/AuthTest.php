<?php

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Auth;


test('can register', function () {
    $userData = [
        'name' => 'Tester',
        'email' => 'mail@crative.app',
        'password' => 'CraftivePass#28094',
        'password_repeat' => 'CraftivePass#28094'
    ];
    $response = $this->postJson(route('auth.register'), $userData);
    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas(
        User::class,
        collect($userData)->except('password', 'password_repeat')->toArray()
    );
});


dataset('register-validation-rules', [
    'name is required' => ['name', ''],
    'name be a string' => ['name', ['name']],
    'name not too short' => ['name', 'ct'],
    'name not too long' => ['name', fn() => longName()],

    'email is required' => ['email', '', 'e'],
    'email be valid' => ['email', 'craftive.app'],
    'email be unique' => ['email', fn() => takenEmail()],

    'password is required' => ['password', ''],
    'password be >=8 chars' => ['password', 'Te12#'],
    'password be uncompromised' => ['password', 'password'],
]);


function takenEmail(): string
{
    $takenEmail = 'taken@craftive.app';
    User::factory()->create(['email' => $takenEmail]);
    return $takenEmail;
}

function longName(): string
{
    return Str::repeat('craftive', rand(min: 15, max: 20));
}

test('register exception is thrown', function (string $field, string|array $value) {
    $userData = [
        'name' => 'Tester',
        'email' => 'mail@crative.app',
        'password' => 'CraftivePass#28094',
        'password_repeat' => 'CraftivePass#28094'
    ];
    $response = $this->postJson(route('auth.register'), [...$userData, $field => $value]);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonStructure(['message', 'errors' => [$field]]);
})->with('register-validation-rules');


test('can login', function () {
    $user = User::factory()->create(['password' => 'test' ]);
    $response = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'test']);
    $response->assertStatus(Response::HTTP_OK);

    $response->assertJsonStructure([
        'accessToken'
    ]);
});

test('can not login with invalid credentials', function () {
    $user = User::factory()->create();
    $response = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'test']);
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test('can not login without credentials', function () {
    User::factory()->create();
    $response = $this->postJson(route('auth.login'), []);
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test('can logout', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $user->currentAccessToken()->shouldReceive('delete')->once();

    $response = $this->postJson(route('auth.logout'));
    $response->assertStatus(Response::HTTP_NO_CONTENT);

});

test('can not logout', function () {
    $response = $this->postJson(route('auth.logout'));
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

});
