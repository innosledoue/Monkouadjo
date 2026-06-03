<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\NlpDriver;

/**
 * Stub OpenAI — actif quand OPENAI_API_KEY est défini.
 * Tombe en fallback sur le driver local si la clé manque ou si l'appel échoue.
 */
class OpenAiNlpDriver implements NlpDriver
{
    public function __construct(
        private readonly LocalNlpDriver $fallback,
        private readonly ?string $apiKey = null,
        private readonly string $model = 'gpt-4o-mini',
    ) {
    }

    public function extractEntities(string $text, ?int $userId = null): array
    {
        if (empty($this->apiKey)) {
            return $this->fallback->extractEntities($text, $userId);
        }

        // TODO : appel OpenAI Chat Completions avec function calling.
        // Pour l'instant, fallback systématique — branchement réel en slice 2.
        $result = $this->fallback->extractEntities($text, $userId);
        $result['driver'] = 'openai-fallback';
        return $result;
    }
}
