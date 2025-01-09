<?php

namespace App\Jobs;

use App\Models\Category;
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

class TranslateCategory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $categoryId;
    protected $lang = 'id';

    /**
     * Create a new job instance.
     *
     * @param int $categoryId
     * @param string $lang
     */
    public function __construct(int $categoryId, string $lang)
    {
        $this->categoryId = $categoryId;
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
            $category = Category::find($this->categoryId);

            if ($category) {
                $categoryDataArray = [];
                $translate = "";

                if ($this->lang === 'id') {
                    $categoryDataArray['title'] = $category->title_id ?? 'N/A';
                    $categoryDataArray['des'] = $category->des_id ?? 'N/A';
                    $categoryDataArray['note'] = $category->note_id ?? 'N/A';
                    $translate = " terjemahan dalam format JSON ke Bahasa Inggris ";
                } else {
                    $categoryDataArray['title'] = $category->title ?? 'N/A';
                    $categoryDataArray['des'] = $category->des ?? 'N/A';
                    $categoryDataArray['note'] = $category->note ?? 'N/A';
                    $translate = "translation to Indonesian in JSON format without changing the structure or field that exists, only translate the content:";
                }

                $typeDataJson = json_encode($categoryDataArray);
                sleep(30);
                $openai = new OpenAIService();
                $response = $openai->getAIResponse($translate . ' ' . $typeDataJson);

                if ($response['status'] === false) {
                    // $openai = new OpenAIService();
                    Log::info('OpenAI failed, falling back to OpenAIService' . $response['data']);
                    // $response = $openai->getAIResponse($translate . ' ' . $typeDataJson);
                    $gemini = new GeminiService();
                    $response = $gemini->getAIResponse($translate . ' ' . $typeDataJson);
                }

                if ($response['status']) {
                    try {
                        // Ensure we're working with a string for json_decode
                        $jsonString = is_string($response['data']) ? $response['data'] : json_encode($response['data']);
                        // Log::info('Translated Type Data: ' . json_encode($jsonString));
                        $decode = json_decode($jsonString, true);
                        Log::info('Translated Type Data: ' . json_encode($decode));

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception('JSON decode error: ' . json_last_error_msg());
                        }

                        // Log as JSON string to avoid array conversion issues
                        Log::info('Translated Type Data oke: ' . json_encode($decode));

                        if ($this->lang === 'id') {
                            $category->title = $response['data']['title'];  // Access the array
                            $category->des = $response['data']['des'];  // Access the array
                            $category->note = $response['data']['note'];  // Access the array

                        } else {
                            $category->title_id = $response['data']['title'];  // Access the array
                            $category->des_id = $response['data']['des'];  // Access the array
                            $category->note_id = $response['data']['note'];  // Access the array
                        }
                        $category->save();
                    } catch (\Exception $jsonError) {
                        Log::error('JSON processing error: ' . $jsonError->getMessage() . $response);
                    }
                } else {
                    Log::error('Translation failed for data: ' . $response);
                }

                if ($response['status'] === false) {
                    Log::info('Gemini failed, falling back to OpenAI');
                    $openai = new OpenAIService();
                    $groqService = new GroqService();
                    $response = $groqService->getAIResponse($typeDataJson . ' ' .   $translate);
                }
                if ($response['status']) {
                    Log::info('Encoded Type Data: ' . $typeDataJson);
                    Log::info('Translated Type Data: ' . json_encode($response['data']));
                    if ($this->lang === 'id') {
                        $category->title = $response['data']['title'];  // Access the array
                        $category->des = $response['data']['des'];  // Access the array
                        $category->note = $response['data']['note'];  // Access the array

                    } else {
                        $category->title_id = $response['data']['title'];  // Access the array
                        $category->des_id = $response['data']['des'];  // Access the array
                        $category->note_id = $response['data']['note'];  // Access the array
                    }
                    $category->save();
                } else {
                    // Tangani error jika gagal
                    Log::error($response['data']);
                }
            }
        } catch (\Exception $e) {
            Log::error('Translation job failed for category ID ' . $this->categoryId . ': ' . $e->getMessage());
        }
    }
}
