<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCustomListRequest;
use App\Http\Requests\StoreCustomListRequest;
use App\Http\Resources\CustomListResource;
use App\Models\CustomList;
use App\Services\CustomListService;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;


class CustomListController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, CustomListService $service)
    {
        $lists = $service->getAll($request->user());
        return response()->json([
            'lists' => CustomListResource::collection($lists),
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
    public function store(SaveCustomListRequest $request, CustomListService $service)
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
    public function update(SaveCustomListRequest $request, CustomList $list, CustomListService $service)
    {
        if ($request->user()->cannot('update', $list)) {
            return response()->json(['message' => 'Você não pode editar essa lista.'], 403);
        }

        $service->update($list, $request->validated('title'));
        return response()->json([
            'list' => (new CustomListResource($list))->toArray($request),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, CustomList $list, CustomListService $service)
    {
        if ($request->user()->cannot('delete', $list)) {
            return response()->json(['message' => ' Você não pode deletar essa lista.'], 403);
        }

        $deleted = $service->delete($list);

        return response()->json([
            'list' => (new CustomListResource($list))->toArray($request),
            'deleted' => $deleted
        ]);
    }
}
