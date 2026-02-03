<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreListInvitationRequest;
use App\Http\Resources\ListInvitationResource;
use App\Models\CustomList;
use App\Models\ListInvitation;
use App\Services\ListInvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ListInvitationsController extends Controller
{
    public function store(StoreListInvitationRequest $request, CustomList $list, ListInvitationService $service)
    {
        if ($request->user()->cannot('shareList', $list)) {
            return response()->json(['message' => 'Você não pode compartilhar essa lista.'], 403);
        }

        $invitation = $service->create($list, $request->validated('max_uses'));

        Log::info('Invitation created', ['invitation' => $invitation]);

        return response()->json([
            'invitation' => new ListInvitationResource($invitation)->toArray($request),
        ]);
    }

    public function show(Request $request, CustomList $list, ListInvitation $invitation)
    {
        return response()->json([
            'invitation' => new ListInvitationResource($invitation)->toArray($request),
        ]);
    }

    public function accept(Request $request, CustomList $list, ListInvitation $invitation, ListInvitationService $service)
    {
        try {
            $accepted = $service->accept($list, $request->user(), $invitation);

            return response()->json([
                'accepted' => $accepted,
            ]);
        } catch (\Exception $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
            return response()->json([
                'message' => $e->getMessage(),
            ], $code);
        }
    }
}
