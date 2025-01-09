<?php

namespace App\Jobs;

use App\Models\Type;
use App\Services\AI\GeminiService;
use App\Services\AI\OpenAIService;
use App\Services\AI\GroqService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslateType implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $typeId;
    protected $lang = 'id';

    /**
     * Create a new job instance.
     *
     * @param int $typeId
     * @param string $lang
     */
    public function __construct(int $typeId, string $lang)
    {
        $this->typeId = $typeId;
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
            $type = Type::find($this->typeId);

            if ($type) {
                $typeDataArray = [];
                $translate = "";

                if ($this->lang === 'id') {
                    $typeDataArray['name'] = $type->name ?? 'N/A';
                    $typeDataArray['title'] = $type->title ?? 'N/A';
                    $typeDataArray['des'] = $type->des ?? 'buatkan deskripsi gambungan dari title yang menarik ';
                    $translate = " terjemahan dalam format JSON ke Bahasa Inggris ";
                } else {
                    $typeDataArray['name'] = $type->name ?? 'N/A';
                    $typeDataArray['title'] = $type->title ?? 'N/A';
                    $typeDataArray['des'] = $type->des ?? 'N/A';
                    $translate = "translation to Indonesian in JSON format without changing the structure or field that exists, only translate the content:";
                }

                $typeDataJson = json_encode($typeDataArray);
                sleep(30);
                $openai = new OpenAIService();
                $response = $openai->getAIResponse($translate . ' ' . $typeDataJson);

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

                        // Update the model with the translated data
                        if ($this->lang === 'id') {
                            $type->name = $decoded['name'];
                            $type->title = $decoded['title'];
                            $type->des = $decoded['des'];
                        } else {
                            $type->name_id = $decoded['name'];
                            $type->title_id = $decoded['title'];
                            $type->des_id = $decoded['des'];
                        }

                        $type->save();
                    } catch (\Exception $jsonError) {
                        Log::error('JSON processing error: ' . $jsonError->getMessage());
                    }
                } else {
                    Log::error('Translation failed for type ID ' . $this->typeId . ': ' . json_encode($response));
                }
            }
        } catch (\Exception $e) {
            Log::error('Translation job failed for type ID ' . $this->typeId . ': ' . $e->getMessage());
        }
    }
}
