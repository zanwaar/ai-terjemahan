<?php

namespace App\Jobs;

use App\Models\Guide;
use App\Services\AI\GeminiService;
use App\Services\AI\GroqService;
use App\Services\AI\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslateGuide implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $guideId;
    protected $lang = 'id';

    /**
     * Create a new job instance.
     *
     * @param int $guideId
     * @param string $lang
     */
    public function __construct(int $guideId, string $lang)
    {
        $this->guideId = $guideId;
        $this->lang = $lang;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $guide = Guide::find($this->guideId);

            if ($guide) {
                $guideDataArray = [];
                $translate = "";

                if ($this->lang === 'id') {
                    $guideDataArray['title'] = $guide->title ?? 'N/A';
                    $guideDataArray['content'] = $guide->content ?? 'N/A';
                    $translate = " terjemahan dalam format JSON ke Bahasa Inggris ";
                } else {
                    $guideDataArray['title'] = $guide->title ?? 'N/A';
                    $guideDataArray['content'] = $guide->content ?? 'N/A';
                    $translate = "translation to Indonesian in JSON format without changing the structure or field that exists, only translate the content:";
                }

                $typeDataJson = json_encode($guideDataArray);
                sleep(30);

                $gemini = new GeminiService();
                $response = $gemini->getAIResponse($translate . ' ' . $typeDataJson);

                if ($response['status'] === false) {
                    Log::info('Gemini failed, falling back to OpenAI');
                    $openai = new OpenAIService();
                    $response = $openai->getAIResponse($translate . ' ' . $typeDataJson);
                }

                if ($response['status']) {
                    try {
                        // Ensure we're working with a string for json_decode
                        $jsonString = is_array($response['data']) ? json_encode($response['data']) : $response['data'];
                        Log::info('Translated Type Data: ' . $jsonString);

                        // Decode the JSON string
                        $decoded = json_decode($jsonString, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception('JSON decode error: ' . json_last_error_msg());
                        }

                        // Log the decoded data
                        Log::info('Decoded Type Data: ' . json_encode($decoded));

                        if ($this->lang === 'id') {
                            $guide->title = $decoded['title'];
                            $guide->content = $decoded['content'];
                        } else {
                            $guide->title_id = $decoded['title'];
                            $guide->content_id = $decoded['content'];
                        }
                        $guide->save();
                    } catch (\Exception $jsonError) {
                        Log::error('JSON processing error: ' . $jsonError->getMessage());
                    }
                } else {
                    Log::error('Translation failed for guide ID ' . $this->guideId . ': ' . json_encode($response));
                }
            }
        } catch (\Exception $e) {
            Log::error('Translation job failed for guide ID ' . $this->guideId . ': ' . $e->getMessage());
        }
    }
}
