<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomListRequest;
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
    public function index(Request $request)
    {
        return response()->json([
            'lists' => $request->user()->lists()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @TODO → FormRequest
     */
    public function create(Request $request)
    {
        return response()->json([
            'list' => [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomListRequest $request)
    {
        $service = app(CustomListService::class);
        $list = $service->create(['data' => $request->validated(), 'user' => $request->user()->uuid]);

        return response()->json(
            [
                'list' => $list->toArray($request),
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
