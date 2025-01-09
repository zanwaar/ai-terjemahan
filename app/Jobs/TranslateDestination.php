<?php

namespace App\Jobs;

use App\Models\Destination;
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

class TranslateDestination implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $destinationId;
    protected $lang = 'id';

    /**
     * Create a new job instance.
     *
     * @param int $destinationId
     * @param string $lang
     */

    public function __construct(int $destinationId, string $lang)

    {
        $this->destinationId = $destinationId;
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
            // Find the destination from the database.
            $destination = Destination::find($this->destinationId);

            if ($destination) {
                $destinationDataArray = [];
                $translate = "";

                // Handle translation for different languages.
                if ($this->lang === 'id') {
                    $destinationDataArray['name'] = $destination->name ?? 'N/A';
                    $destinationDataArray['des'] = $destination->des ?? 'N/A';
                    $destinationDataArray['note'] = $destination->note ?? 'N/A';
                    $translate = " terjemahan dalam format JSON ke Bahasa Inggris ";
                } else {
                    $destinationDataArray['name'] = $destination->name ?? 'N/A';
                    $destinationDataArray['des'] = $destination->des ?? 'N/A';
                    $destinationDataArray['note'] = $destination->note ?? 'N/A';
                    $translate = "translation to Indonesian in JSON format without changing the structure or field that exists, only translate the content:";
                }

                // Encode the destination data to JSON.
                $typeDataJson = json_encode($destinationDataArray);
                Log::info('Sending translation request: ' . $typeDataJson);

                // Wait before making the next request, if necessary (not always recommended).
                sleep(10);

                // Get translation from OpenAI service.
                $openai = new OpenAIService();
                $response = $openai->getAIResponse($translate . ' ' . $typeDataJson);

                // Fallback to Gemini if OpenAI fails.
                if ($response['status'] === false) {
                    Log::info('OpenAI failed, falling back to Gemini');
                    $gemini = new GeminiService();
                    $response = $gemini->getAIResponse($translate . ' ' . $typeDataJson);
                }

                // Process the response from either service.
                if ($response['status'] && !empty($response['data'])) {
                    try {
                        // Ensure we are working with a string for json_decode.
                        $jsonString = is_array($response['data']) ? json_encode($response['data']) : $response['data'];
                        Log::info('Translated Type Data: ' . $jsonString);

                        // Decode the JSON response.
                        $decode = json_decode($jsonString, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception('JSON decode error: ' . json_last_error_msg());
                        }

                        // Log decoded data for debugging.
                        Log::info('Decoded Type Data: ' . json_encode($decode));

                        // Update destination data based on the language.
                        if ($this->lang === 'id') {
                            $destination->name = $decode['name'] ?? 'N/A';
                            $destination->des = $decode['des'] ?? 'N/A';
                            $destination->note = $decode['note'] ?? 'N/A';
                        } else {
                            $destination->name_id = $decode['name'] ?? 'N/A';
                            $destination->des_id = $decode['des'] ?? 'N/A';
                            $destination->note_id = $decode['note'] ?? 'N/A';
                        }

                        // Save the updated destination.
                        $destination->save();
                    } catch (\Exception $jsonError) {
                        Log::error('JSON processing error: ' . $jsonError->getMessage());
                    }
                } else {
                    Log::error('Translation failed for data: ' . json_encode($response));
                }
            }
        } catch (\Exception $e) {
            Log::error('Translation job failed for destination ID ' . $this->destinationId . ': ' . $e->getMessage());
            throw $e;  // Ensure the exception is thrown after logging it.
        }
    }
}
