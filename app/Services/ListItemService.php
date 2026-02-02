<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CustomList;
use App\Models\ListItem;
use Illuminate\Support\Facades\DB;

final class ListItemService
{

    public function add(CustomList $list, string $name, ?string $description = null): ListItem
    {
        return DB::transaction(function () use ($list, $name, $description) {
            $item = $list->items()->create([
                'name' => $name,
                'description' => $description,
                'completed' => false,
                'version' => 1,
            ]);

            return $item;
        });
    }
}
