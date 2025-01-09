<?php

namespace App\Services\AI;

use GuzzleHttp\Client;

class GeminiService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('GEMINI_API_KEY');
    }

    /**
     * Method untuk memanggil Google Generative Language API
     */
    public function getAIResponse($input)
    {
        // Endpoint API
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-002:generateContent?key={$this->apiKey}";

        // Data yang akan dikirim ke API
        $data = [
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        [
                            "text" => $input
                        ]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 1,
                "topK" => 40,
                "topP" => 0.95,
                "maxOutputTokens" => 8192,
                "responseMimeType" => "application/json"
            ]
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $data
            ]);


            $body = $response->getBody();
            $content = json_decode($body->getContents(), true);

            $resultText = $content['candidates'][0]['content']['parts'][0]['text'];
            $jsonResult = json_decode($resultText, true);
            return [
                'status' => true,
                'data' => $jsonResult,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'data' => 'Failed to get response from OpenAI API: ' . $e->getMessage(),
            ];
        }
    }
}
