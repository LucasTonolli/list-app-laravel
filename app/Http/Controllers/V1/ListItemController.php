<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\ItemversionMismatchException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListItemRequest;
use App\Http\Requests\UpdateListItemRequest;
use App\Http\Resources\ListItemResource;
use App\Http\Requests\BulkStoreListItemRequest;
use App\Models\CustomList;
use App\Models\ListItem;
use App\Services\ListItemService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ListItemController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreListItemRequest $request, CustomList $list, ListItemService $service)
    {
        $this->authorize('updateItems', $list);

        $item = $service->add($list, $request->validated('name'), $request->validated('description'));

        return response()->json([
            'item' => (new ListItemResource($item))->toArray($request),
        ], status: 201);
    }

    public function bulkStore(BulkStoreListItemRequest $request, CustomList $list, ListItemService $service)
    {
        $this->authorize('updateItems', $list);

        $items = $service->bulkAdd($list, $request->validated('items'));

        return response()->json([
            'items' => ListItemResource::collection($items)->toArray($request),
        ], status: 201);
    }

    public function toggle(Request $request, CustomList $list, ListItem $item, ListItemService $service)
    {
        $this->authorize('updateItems', $list);

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
     * Update the specified resource in storage.
     */
    public function update(UpdateListItemRequest $request, CustomList $list, ListItem $item, ListItemService $service)
    {
        $this->authorize('updateItems', $list);

        try {
            $updated = $service->update($item, $request->validated('name'), $request->validated('description'), $request->validated('version'));
        } catch (ItemversionMismatchException $e) {
            Log::error($e);
            return response()->json(['message' => 'A versão do item está errada.'], 409);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['message' => 'Ocorreu um erro ao atualizar o item.'], 500);
        }

        return response()->json([
            'item' => (new ListItemResource($item))->toArray($request),
            'updated' => $updated
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, CustomList $list, ListItem $item, ListItemService $service)
    {
        $this->authorize('updateItems', $list);

        $deleted = $service->delete($item);

        return response()->json([
            'item' => (new ListItemResource($item))->toArray($request),
            'deleted' => $deleted
        ]);
    }
}
