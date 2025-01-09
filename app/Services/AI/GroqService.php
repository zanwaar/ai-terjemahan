<?php

namespace App\Services\AI;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('GROQ_API_KEY'); // Pastikan API key ada di .env
    }

    /**
     * Method untuk memanggil Groq API
     */
    public function getAIResponse($input)
    {
        // Endpoint API untuk Groq
        $url = "https://api.groq.com/openai/v1/chat/completions";

        // Data yang akan dikirim ke API
        $data = [
            "messages" => [
                [
                    "role" => "user",
                    "content" => $input
                ],
                [
                    "role" => "assistant",
                    "content" => "" // Kosongkan content untuk memastikan bot memberikan jawaban
                ],
                [
                    "role" => "assistant",
                    "content" => "{\n  \"name\": \"PERBODA CEPAT & KAJUH\"\n}" // Respons dari assistant, sesuaikan dengan hasil terjemahan
                ]
            ],
            "model" => "llama-3.3-70b-versatile", // Model yang digunakan
            "temperature" => 1,
            "max_tokens" => 1024,
            "top_p" => 1,
            "stream" => false,
            "response_format" => [
                "type" => "json_object"
            ],
            "stop" => null
        ];

        try {
            // Mengirimkan permintaan ke Groq API
            $response = $this->client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
                'json' => $data,
            ]);

            // Mendapatkan body dari respons
            $body = $response->getBody();
            $content = json_decode($body->getContents(), true);

            // Log respons untuk debug
            // Log::info('Groq API Response: ' . json_encode($content));

            // Ambil hasil terjemahan dari response
            $resultText = $content['choices'][0]['message']['content']; // Hasil dari Groq
            $jsonResult = json_decode($resultText, true);

            return [
                'status' => true,
                'data' => $jsonResult, // Mengembalikan data yang sudah ter-parse
            ];
        } catch (\Exception $e) {
            // Jika gagal, kirimkan status false dan pesan error
            Log::error('Groq API Error: ' . $e->getMessage());
            return [
                'status' => false,
                'data' => 'Failed to get response from Groq API: ' . $e->getMessage(),
            ];
        }
    }
}
