<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\CustomListResource;
use App\Models\CustomList;
use Illuminate\Support\Facades\Log;

final class CustomListService
{
    public function create(string $title, string $userUuid): CustomList
    {

        $list = CustomList::create([
            'title' => $title,
            'owner_uuid' => $userUuid,
        ]);

        $list->owner()->associate(
            $userUuid,
            ['role' => 'owner']
        );

        Log::info('List created', [
            'list' => $list,
            'owner' => $list->owner
        ]);
        return $list;
    }
}
