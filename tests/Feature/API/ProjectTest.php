<?php

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

function projectStructure(bool $full = true)
{
    $structure = [
        'id',
        'user_id',
        'name',
        'created_at',
        'updated_at',
    ];
    return $full ? array_merge($structure, ['user']) : $structure + ['user'];
}


dataset('project-validation-rules', [
    'name is required' => ['name', ''],
    'name be a string' => ['name', ['name']],
    'name not too short' => ['name', 'ct'],
    'name not too long' => ['name', fn() => longName(min: 40, max: 60)],
]);

dataset('project-routes', [
    'projects.index',
    'projects.store',
    'projects.show',
    'projects.update',
    'projects.destroy',
]);

test('route has api and auth middleware', function (String $route) {
    expect($route)->toHaveExactMiddlewares(['api', 'auth:sanctum']);
})->with('project-routes');

test('can list own projects', function () {
    $user = User::factory()->hasProjects(3)->create();
    Project::factory(2)->create();
    Sanctum::actingAs($user);
    $response = $this->getJson(route('projects.index'));
    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount(1);
    $response->assertJsonCount(3, 'data');
    $response
        ->assertJson(
            fn(AssertableJson $json) =>
            $json->has(
                'data',
                fn(AssertableJson $json) =>
                $json->each(
                    fn(AssertableJson $json) => $json->hasAll(projectStructure(full: false))
                )
            )
        );
});

test('can create a project', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $projectName = fake()->words(2, true);
    $response = $this->postJson(route('projects.store'), [
        'name' => $projectName
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $response->assertJsonStructure(['data' => projectStructure(full: false)]);

    $this->assertDatabaseHas(Project::class, [
        'name' => $projectName,
        'user_id' => $user->id
    ]);
});

test('can create exception is thrown', function ($field, $value) {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $projectData = [
        'name' => 'Project A',
    ];

    $response = $this->postJson(route('projects.store'), [...$projectData, $field => $value]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonStructure(['message', 'errors' => [$field]]);
})->with('project-validation-rules');

test('can view own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id
    ]);
    Sanctum::actingAs($user);
    $response = $this->getJson(route('projects.show', ['project' => $project->id]));
    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonStructure(['data' => projectStructure()]);
});

test('can not view others project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' =>  User::factory()->create()->id
    ]);
    Sanctum::actingAs($user);
    $response = $this->getJson(route('projects.show', ['project' => $project->id]));
    $response->assertNotFound();
});


test('can update own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id
    ]);
    Sanctum::actingAs($user);
    $response = $this->putJson(
        route('projects.update', ['project' => $project->id]),
        [
            'name' => 'updated name'
        ]
    );
    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonStructure(['data' => projectStructure()]);

    $this->assertDatabaseHas(Project::class, [
        'id' => $project->id,
        'name' => 'updated name',
        'user_id' => $user->id
    ]);
});

test('update exception is thrown', function ($field, $value) {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id
    ]);

    Sanctum::actingAs($user);

    $projectData = [
        'name' => 'Project A',
    ];

    $response = $this->putJson(
        route('projects.update', ['project' => $project->id]),
        [...$projectData, $field => $value]
    );

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonStructure(['message', 'errors' => [$field]]);
})->with('project-validation-rules');

test('can not update others project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' =>  User::factory()->create()->id
    ]);
    Sanctum::actingAs($user);
    $response = $this->putJson(
        route('projects.update', ['project' => $project->id]),
        [
            'name' => 'updated name'
        ]
    );
    $response->assertNotFound();
});

test('can delete own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' => $user->id
    ]);
    Sanctum::actingAs($user);
    $response = $this->deleteJson(
        route('projects.destroy', ['project' => $project->id]),
    );
    $response->assertStatus(Response::HTTP_NO_CONTENT);

    $this->assertDatabaseMissing(Project::class, [
        'id' => $project->id,
    ]);
});

test('can not delete others project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'user_id' =>  User::factory()->create()->id
    ]);
    Sanctum::actingAs($user);
    $response = $this->deleteJson(
        route('projects.destroy', ['project' => $project->id]),
    );
    $response->assertNotFound();
});


