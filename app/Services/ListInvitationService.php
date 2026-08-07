<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Roles;
use App\Exceptions\AlreadySharedException;
use App\Exceptions\InvalidInvitationException;
use App\Exceptions\InvitationExpiredException;
use App\Exceptions\InvitationMaxUseException;
use App\Exceptions\OwnerAcceptException;
use App\Models\CustomList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
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

        return (bool) DB::transaction(function () use ($list, $user, $invitation) {

            if ($invitation->custom_list_uuid !== $list->uuid) {
                throw new InvalidInvitationException();
            }

            $affected = ListInvitation::where('uuid', $invitation->uuid)
                ->where('uses', '<', $invitation->max_uses)
                ->increment('uses');

            if (!$affected) {
                throw new InvitationMaxUseException();
            }

            try {
                $list->sharedWith()->attach(
                    $user->uuid,
                    ['role' => Roles::Editor]
                );
            } catch (UniqueConstraintViolationException) {
                throw new AlreadySharedException();
            }

            return true;
        });
    }
}
