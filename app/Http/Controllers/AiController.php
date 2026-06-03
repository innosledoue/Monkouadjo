<?php

namespace App\Http\Controllers;

use App\Services\Ai\Contracts\NlpDriver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function parseText(Request $request, NlpDriver $nlp): JsonResponse
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:2000'],
        ]);

        $result = $nlp->extractEntities($data['text'], $request->user()->id);

        return response()->json($result);
    }
}
