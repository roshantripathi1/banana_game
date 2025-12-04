<?php
// services/BananaService.php
// Uses the Banana API: http://marcconrad.com/uob/banana/api.php
// Only JSON is used in this project.

class BananaService
{
    // Base Banana API URL
    private const BASE_URL = "http://marcconrad.com/uob/banana/api.php";

    /**
     * Get puzzle in JSON format.
     * out=json (default)
     * base64=yes/no
     */
    public static function getPuzzleJson(bool $useBase64 = false): array
    {
        // Prepare query
        $query = [
            'out'    => 'json',                        // JSON output
            'base64' => $useBase64 ? 'yes' : 'no'      // base64 optional
        ];

        $url = self::BASE_URL . '?' . http_build_query($query);

        // Server-side HTTP call (avoids CORS)
        $response = @file_get_contents($url);

        if ($response === false) {
            return [
                'answer'       => null,
                'image_url'    => null,
                'image_base64' => null,
                'raw'          => null,
                'error'        => 'Unable to reach Banana API'
            ];
        }

        // Decode JSON from Banana API
        $data = json_decode($response, true);

        // Extract values as defined by API spec
        $answer      = isset($data['answer']) ? (int)$data['answer'] : null;
        $imageUrl    = $data['question'] ?? null;   // URL to puzzle image
        $imageBase64 = $data['image']   ?? null;    // base64 if requested

        return [
            'answer'       => $answer, 
            'image_url'    => $imageUrl,
            'image_base64' => $imageBase64,
            'raw'          => $data,
            'error'        => null
        ];
    }
}
