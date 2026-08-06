<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCustomListRequest;
use App\Http\Resources\CustomListResource;
use App\Models\CustomList;
use App\Services\CustomListService;
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
    public function show(Request $request, CustomList $list)
    {
        $this->authorize('view', $list);

        $list->load(['items', 'sharedWith']);
        $list->loadCount(['items', 'sharedWith']);

        return response()->json([
            'list' => (new CustomListResource($list))->toArray($request),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaveCustomListRequest $request, CustomList $list, CustomListService $service)
    {
        $this->authorize('update', $list);

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
        $this->authorize('delete', $list);

        $service->delete($list);

        return response()->json(status: 204);
    }
}
