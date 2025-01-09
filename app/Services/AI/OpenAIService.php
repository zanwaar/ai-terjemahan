<?php

namespace App\Services\AI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getAIResponse($input)
    {
        try {
            // Send a request to the OpenAI API
            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo', // Use a valid model name
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $input // Content must be a plain string
                        ]
                    ],
                    'temperature' => 1,
                    'max_tokens' => 4096,
                    'top_p' => 1,
                    'frequency_penalty' => 0,
                    'presence_penalty' => 0
                ]
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);
            $content = $responseBody['choices'][0]['message']['content'] ?? null;

            return [
                'status' => true,
                'data' => $content,
            ];
        } catch (RequestException $e) {
            return [
                'status' => false,
                'data' => 'Failed to get response from OpenAI API: ' . $e->getMessage(),
            ];
        }
    }
}
