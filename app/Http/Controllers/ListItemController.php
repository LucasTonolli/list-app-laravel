<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreListItemRequest;
use App\Http\Resources\ListItemResource;
use App\Models\CustomList;
use App\Models\ListItem;
use App\Services\ListItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ListItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreListItemRequest $request, CustomList $list, ListItemService $service)
    {
        if ($request->user()->cannot('updateItems', $list)) {
            return response()->json(['message' => 'Você não pode editar essa lista.'], 403);
        }

        $item = $service->add($list, $request->validated('name'), $request->validated('description'));

        return response()->json([
            'item' => (new ListItemResource($item))->toArray($request),
        ], status: 201);
    }

    public function toggle(Request $request, CustomList $list, ListItem $item, ListItemService $service)
    {
        if ($request->user()->cannot('updateItems', $list)) {
            return response()->json(['message' => 'Sem permissão.'], 403);
        }

        if ($item->custom_list_uuid !== $list->uuid) {
            return response()->json(['message' => 'Sem permissão.'], 403);
        }

        $toggle = $service->toggle($item);

        return response()->json([
            'item' => (new ListItemResource($item))->toArray($request),
            'toggle' => $toggle
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
