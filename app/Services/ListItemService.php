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

    public function toggle(ListItem $item): bool
    {
        return (bool) $item->update([
            'completed' => !$item->completed,
            'version' => $item->version + 1,
        ]);
    }

    public function delete(ListItem $item): bool
    {
        return (bool) $item->delete();
    }

    public function update(ListItem $item, string $name, ?string $description = null, int $version): bool
    {
        $updated = ListItem::where('uuid', $item->uuid)
            ->where('version', $version)
            ->update([
                'name' => $name,
                'description' => $description,
                'version' => $version + 1,
            ]);
        if (!$updated) {
            throw new \Exception('Item version mismatch', 409);
        }

        $item->refresh();

        return (bool) $updated;
    }
}
