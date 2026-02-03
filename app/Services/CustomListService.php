<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CustomList;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

final class CustomListService
{
    public function create(string $title, string $userUuid): CustomList
    {
        return DB::transaction(function () use ($title, $userUuid) {
            $list = CustomList::create([
                'title' => $title,
                'owner_uuid' => $userUuid,
            ]);

            $list->sharedWith()->attach(
                $userUuid,
                ['role' => 'owner']
            );

            return $list;
        });
    }

    public function get(string $id): CustomList
    {
        return CustomList::with(['items', 'sharedWith'])->findOrFail($id);
    }

    public function getAll(User $user): Collection
    {
        return $user->lists()->withCount(['items', 'sharedWith'])->get();
    }

    public function delete(CustomList $list): bool
    {
        return (bool) $list->delete();
    }

    public function update(CustomList $list, string $title): bool
    {
        return (bool) $list->update([
            'title' => $title,
        ]);
    }
}
