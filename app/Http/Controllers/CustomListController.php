<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomListRequest;
use App\Http\Resources\CustomListResource;
use App\Services\CustomListService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\HttpCache\Store;;

class CustomListController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, CustomListService $service)
    {
        return response()->json([
            'lists' => $service->getAll($request->user()->uuid)->toResourceCollection(CustomListResource::class),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @TODO → FormRequest
     */
    public function create(Request $request)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomListRequest $request, CustomListService $service)
    {
        $list = $service->create($request->validated('title'), $request->user()->uuid);

        return response()->json(
            [
                'list' => (new CustomListResource($list))->toArray($request),
            ],
            status: 201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return response()->json([
            'list' => [],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return response()->json([
            'list' => [],
            'deleted' => true
        ]);
    }
}
