<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var Illuminate\Auth\AuthManager $auth */
        $auth = auth();
        $projects = $auth->user()->projects;
        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request)
    {

        $project = $request()->user()->projects()->create($request->validated());
        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        if ($this->projectBelongsToUser($project)) {
            return new ProjectResource($project);

        }
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, Project $project)
    {
        if ($this->projectBelongsToUser($project)) {
            $project->update($request->validated());
            return new ProjectResource($project);
        }
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        if ($this->projectBelongsToUser($project)) {
            $project->delete();
            return response()->noContent();
        }
        abort(404);
    }


    private function projectBelongsToUser(Project $project)
    {
        /** @var Illuminate\Auth\AuthManager $auth */
        $auth = auth();
        return $auth->user()->id === $project->user->id;
    }
}
