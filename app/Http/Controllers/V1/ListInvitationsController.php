<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListInvitationRequest;
use App\Http\Resources\ListInvitationResource;
use App\Models\CustomList;
use App\Models\ListInvitation;
use App\Services\ListInvitationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth:sanctum', only: ['store', 'accept'])]
#[Middleware('throttle:api', only: ['store', 'accept'])]
class ListInvitationsController extends Controller
{
    use AuthorizesRequests;
    public function store(StoreListInvitationRequest $request, CustomList $list, ListInvitationService $service)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Usuário não autenticado.'], 401);
        }

        $this->authorize('shareList', $list);

        $invitation = $service->create($list, $request->validated('max_uses'), $request->validated('expires_in_minutes'));

        return response()->json(data: [
            'invitation' => new ListInvitationResource($invitation)->toArray($request)
        ]);
    }

    /* Público de propósito: permite pré-visualizar o convite (título + accept_url) antes de
    * logar/cadastrar, como nos links de convite do Google Docs/Notion. A segurança depende
    * só da entropia do token (128 bits, ver ListInvitationService::create). accept() é a
    * ação que muda estado e por isso exige auth:sanctum (linha 15). */
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
