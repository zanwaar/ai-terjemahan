<?php

namespace App\Jobs;

use App\Models\Event;
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

class TranslateEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $eventId;
    protected $lang = 'id';

    /**
     * Create a new job instance.
     *
     * @param int $eventId
     * @param string $lang
     */
    public function __construct(int $eventId, string $lang)
    {
        $this->eventId = $eventId;
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
            $event = Event::find($this->eventId);

            if ($event) {
                $eventDataArray = [];
                $translate = "";

                if ($this->lang === 'id') {
                    $eventDataArray['title'] = $event->title ?? 'N/A';
                    $eventDataArray['content'] = $event->content ?? 'N/A';
                    $translate = " terjemahan dalam format JSON ke Bahasa Inggris ";
                } else {
                    $eventDataArray['title'] = $event->title ?? 'N/A';
                    $eventDataArray['content'] = $event->content ?? 'N/A';
                    $translate = "translation to Indonesian in JSON format without changing the structure or field that exists, only translate the content:";
                }
                $typeDataJson = json_encode($eventDataArray);
                sleep(30);
                $groqService = new GroqService();
                $geminai = new GeminiService();
                $response = $geminai->getAIResponse($translate . ' ' . $typeDataJson);


                if ($response['status'] === false) {
                    Log::info('Gemini failed, falling back to OpenAI');
                    $openai = new OpenAIService();
                    $groqService = new GroqService();
                    $response = $groqService->getAIResponse($translate . ' ' .   $typeDataJson);
                }
                if ($response['status']) {
                    Log::info('Encoded Type Data: ' . $typeDataJson);
                    Log::info('Translated Type Data: ' . json_encode($response['data']));
                    if ($this->lang === 'id') {
                        $event->title = $response['data']['title'];  // Access the array
                        $event->content = $response['data']['content'];
                    } else {
                        $event->title_id = $response['data']['title'];  // Access the array
                        $event->content_id = $response['data']['content'];  // Access the array

                    }
                    $event->save();
                } else {
                    // Tangani error jika gagal
                    Log::error($response['data']);
                }
            }
        } catch (\Exception $e) {
            Log::error('Translation job failed for event ID ' . $this->eventId . ': ' . $e->getMessage());
        }
    }
}
