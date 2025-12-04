<?php
// services/BananaService.php
// Simple wrapper to call Banana API server-side.

class BananaService
{
    // API URL with JSON output
    private const API_URL = "https://marcconrad.com/uob/banana/api.php?out=json";

    // Get one puzzle
    public static function getPuzzle(): array
    {
        $response = @file_get_contents(self::API_URL);

        if ($response === false) {
            return [
                'answer'    => null,
                'image_url' => null,
                'raw'       => null,
                'error'     => 'Unable to reach Banana API'
            ];
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            return [
                'answer'    => null,
                'image_url' => null,
                'raw'       => $response,
                'error'     => 'Invalid JSON from Banana API'
            ];
        }

        // API uses "solution"
        $answer = null;
        if (isset($data['solution'])) {
            $answer = (int) $data['solution'];
        } elseif (isset($data['answer'])) {
            $answer = (int) $data['answer'];
        }

        $imageUrl = $data['question'] ?? null;

        return [
            'answer'    => $answer,
            'image_url' => $imageUrl,
            'raw'       => $data,
            'error'     => null
        ];
    }
}
