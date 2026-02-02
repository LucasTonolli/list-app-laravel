<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CustomList;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

            Log::info('List created', [
                'list' => $list,
                'sharedWith' => $list->sharedWith,
            ]);
            return $list;
        });
    }

    public function getAll(string $userUuid): Collection
    {
        $user = User::where('uuid', $userUuid)->first();
        return $user->lists()->get();
    }

    public function delete(CustomList $list): bool
    {
        return DB::transaction(function () use ($list) {
            return $list->delete();
        });
    }

    public function update(CustomList $list, string $title): bool
    {
        return DB::transaction(function () use ($list, $title) {
            return $list->update([
                'title' => $title,
            ]);
        });
    }
}
