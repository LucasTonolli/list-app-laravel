<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AlreadySharedException;
use App\Exceptions\InvalidInvitationException;
use App\Exceptions\InvitationExpiredException;
use App\Exceptions\InvitationMaxUseException;
use App\Exceptions\OwnerAcceptException;
use App\Models\CustomList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ListInvitationService
{
    public function create(CustomList $list, ?int $maxUses = 1, ?int $minutesForExpire = 5): ListInvitation
    {
        return DB::transaction(function () use ($list, $maxUses, $minutesForExpire) {
            return $list->invitations()->create([
                'token' => bin2hex(random_bytes(16)),
                'max_uses' => $maxUses ?? 1,
                'expires_at' => now()->addMinutes($minutesForExpire ?? 5)
            ]);
        });
    }

    public function accept(CustomList $list, User $user, ListInvitation $invitation): bool
    {
        if ($user->uuid === $list->owner_uuid) {
            throw new OwnerAcceptException();
        }

        if ($invitation->max_uses === $invitation->uses) {
            throw new InvitationMaxUseException();
        }

        if ($invitation->expires_at < now()) {
            throw new InvitationExpiredException();
        }

        if ($list->sharedWith()->where('user_uuid', $user->uuid)->exists()) {
            throw new AlreadySharedException();
        }

        if ($invitation->custom_list_uuid !== $list->uuid) {
            throw new InvalidInvitationException();
        }

        return (bool) DB::transaction(function () use ($list, $user, $invitation) {

            $affected = $invitation->where('uuid', $invitation->uuid)
                ->where('uses', '<', $invitation->max_uses)
                ->exists();

            if (!$affected) {
                throw new InvitationMaxUseException();
            }

            $list->sharedWith()->attach(
                $user->uuid,
                ['role' => 'editor']
            );

            $invitation->increment('uses');

            return true;
        });
    }
}
