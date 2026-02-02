<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\CustomListResource;
use App\Models\CustomList;
use Illuminate\Support\Facades\Log;

final class CustomListService
{
    public function create(array $data): CustomListResource
    {

        $list = CustomList::create([
            'title' => $data['data']['title'],
            'owner_uuid' => $data['user'],
        ]);

        $resource = new CustomListResource($list);

        Log::info('List created', [
            'list' => $resource,
        ]);
        return $resource;
    }
}
