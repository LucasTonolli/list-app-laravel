<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CustomList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ListInvitationService
{
    public function create(CustomList $list, ?int $maxUses = 1): ListInvitation
    {
        return DB::transaction(function () use ($list, $maxUses) {
            return $list->invitations()->create([
                'token' => bin2hex(random_bytes(16)),
                'max_uses' => $maxUses ?? 1,
                'expires_at' => now()->addMinutes(5)
            ]);
        });
    }

    public function accept(CustomList $list, User $user, ListInvitation $invitation): bool
    {
        if ($user->uuid === $list->owner_uuid) {
            throw new \Exception('Usuário proprietário da lista.', 409);
        }

        if ($invitation->max_uses === $invitation->uses) {
            throw new \Exception('Compartilhamento excedido.', 409);
        }

        if ($invitation->expires_at < now()) {
            throw new \Exception('Link de compartilhamento expirado.', 409);
        }

        if ($list->sharedWith()->where('user_uuid', $user->uuid)->exists()) {
            throw new \Exception('Usuário já compartilha essa lista.', 409);
        }



        return (bool) DB::transaction(function () use ($list, $user, $invitation) {

            $affected = $invitation->where('uuid', $invitation->uuid)
                ->where('uses', '<', $invitation->max_uses);

            if (!$affected) {
                throw new \Exception('Limite de convites atingido.', 409);
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
