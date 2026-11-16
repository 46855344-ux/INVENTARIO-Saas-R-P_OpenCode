<?php

namespace App\Modules\Metrics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Metrics\Http\Requests\ChatQueryRequest;
use App\Modules\Metrics\Services\ChatAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatAssistantController extends Controller
{
    public function __construct(private readonly ChatAssistantService $assistant) {}

    public function query(ChatQueryRequest $request): JsonResponse
    {
        $context = TenantContext::fromRequest($request);
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => $this->assistant->ask($context, $user, $request->string('question')->toString()),
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        return response()->json(['data' => [
            '¿Cuántos productos agotados hay?',
            '¿Qué existencias están bajo el mínimo?',
            '¿Cuáles fueron las salidas de los últimos 30 días?',
            'Dame un resumen del inventario.',
            'Stock de café',
        ]]);
    }
}
