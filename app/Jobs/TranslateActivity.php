<?php

namespace App\Jobs;

use App\Models\Activity;
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

class TranslateActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $activityId;
    protected $lang = 'id';

    /**
     * Create a new job instance.
     *
     * @param int $activityId
     * @param string $lang
     */
    public function __construct(int $activityId, string $lang)
    {
        $this->activityId = $activityId;
        $this->lang = $lang;
    }


    // Fungsi untuk memecah teks menjadi bagian-bagian yang lebih kecil
    function chunkText($text, $chunkSize = 4000)
    {
        // Memecah teks menjadi bagian-bagian kecil berdasarkan ukuran chunk yang diinginkan
        $chunks = [];
        $start = 0;
        $length = strlen($text);

        while ($start < $length) {
            // Tentukan bagian yang akan diambil berdasarkan ukuran chunk yang diinginkan
            $chunk = substr($text, $start, $chunkSize);
            $chunks[] = $chunk;
            $start += $chunkSize;
        }

        return $chunks;
    }



    // public function handle()
    // {
    //     try {
    //         $activity = Activity::find($this->activityId);

    //         if ($activity) {
    //             $activityDataArray = [];
    //             $translate = "";

    //             if ($this->lang === 'id') {

    //                 $activityDataArray['activity_des'] = $activity->activity_des_id ?? 'N/A';
    //                 // $activityDataArray['note'] = $activity->note_id ?? 'N/A ';

    //                 // $activityDataArray['name'] = $activity->name_id ?? 'N/A f';
    //                 // $activityDataArray['short_des'] = $activity->short_des_id ?? 'N/A';
    //                 // $activityDataArray['included'] = $activity->included_id ?? 'N/A';
    //                 // $activityDataArray['whattobring'] = $activity->whattobring_id ?? 'N/A';
    //                 // $activityDataArray['highlight'] = $activity->highlight_id ?? 'N/A';
    //                 // $activityDataArray['pickup_time'] = $activity->pickup_time_id ?? 'N/A';
    //                 // $activityDataArray['confirmation_detail'] = $activity->confirmation_detail_id ?? 'N/A';
    //                 // $activityDataArray['how_to_use'] = $activity->how_to_use_id ?? 'N/A';
    //                 // $activityDataArray['payment'] = $activity->payment_id ?? 'N/A';

    //                 $translate = " terjemahan dalam format JSON ke Bahasa Inggris ";

    //                 $activityDataArray['activity_des'] = $activity->activity_des ?? 'N/A';
    //                 // $activityDataArray['note'] = $activity->note ?? 'N/A ffff';

    //                 // $activityDataArray['name'] = $activity->name ?? 'N/A t';
    //                 // $activityDataArray['short_des'] = $activity->short_des ?? 'N/A';
    //                 // $activityDataArray['included'] = $activity->included ?? 'N/A';
    //                 // $activityDataArray['whattobring'] = $activity->whattobring ?? 'N/A';
    //                 // $activityDataArray['highlight'] = $activity->highlight ?? 'N/A';
    //                 // $activityDataArray['pickup_time'] = $activity->pickup_time ?? 'N/A';
    //                 // $activityDataArray['confirmation_detail'] = $activity->confirmation_detail ?? 'N/A';
    //                 // $activityDataArray['how_to_use'] = $activity->how_to_use ?? 'N/A';
    //                 // $activityDataArray['payment'] = $activity->payment ?? 'N/A';

    //                 // $translate = "terjemahan ke Bahasa Indonesia dalam format JSON tanpa ubah struktur atau filed yang ada, hanya ubah isi data:";
    //                 $translate = "translation to Indonesian in JSON format without changing the structure or field that exists, only translate the content:";
    //             }

    //             $typeDataJson = json_encode($activityDataArray);
    //             sleep(10);
    //             $openai = new OpenAIService();
    //             $response = $openai->getAIResponse($translate . ' ' . $typeDataJson);


    //             if ($response['status'] === false) {
    //                 // $openai = new OpenAIService();
    //                 Log::info('OpenAI failed, falling back to OpenAIService' . $response['data']);
    //                 // $response = $openai->getAIResponse($translate . ' ' . $typeDataJson);
    //                 $gemini = new GeminiService();
    //                 $response = $gemini->getAIResponse($translate . ' ' . $typeDataJson);
    //             }

    //             if ($response['status']) {
    //                 try {
    //                     // Ensure we're working with a string for json_decode
    //                     $jsonString = is_string($response['data']) ? $response['data'] : json_encode($response['data']);
    //                     // Log::info('Translated Type Data: ' . json_encode($jsonString));
    //                     $decode = json_decode($jsonString, true);
    //                     Log::info('Translated Type Data: ' . json_encode($decode));

    //                     if (json_last_error() !== JSON_ERROR_NONE) {
    //                         throw new \Exception('JSON decode error: ' . json_last_error_msg());
    //                     }

    //                     // Log as JSON string to avoid array conversion issues
    //                     Log::info('Translated Type Data oke: ' . json_encode($decode));

    //                     if ($this->lang === 'id') {
    //                         $activity->activity_des = $decode['activity_des'];
    //                         // $activity->note = $decode['note'];

    //                         // $activity->name = $decode['name'];
    //                         // $activity->short_des = $decode['short_des'];
    //                         // $activity->included = $decode['included'];
    //                         // $activity->whattobring = $decode['whattobring'];
    //                         // $activity->highlight = $decode['highlight'];
    //                         // $activity->pickup_time = $decode['pickup_time'];
    //                         // $activity->confirmation_detail = $decode['confirmation_detail'];
    //                         // $activity->how_to_use = $decode['how_to_use'];
    //                         // $activity->payment = $decode['payment'];
    //                     } else {
    //                         $activity->activity_des_id = $decode['activity_des'];
    //                         // $activity->note_id = $decode['note'];

    //                         // $activity->name_id = $decode['name'];
    //                         // $activity->short_des_id = $decode['short_des'];
    //                         // $activity->included_id = $decode['included'];
    //                         // $activity->whattobring_id = $decode['whattobring'];
    //                         // $activity->highlight_id = $decode['highlight'];
    //                         // $activity->pickup_time_id = $decode['pickup_time'];
    //                         // $activity->confirmation_detail_id = $decode['confirmation_detail'];
    //                         // $activity->how_to_use_id = $decode['how_to_use'];
    //                         // $activity->payment_id = $decode['payment'];
    //                     }

    //                     $activity->save();
    //                 } catch (\Exception $jsonError) {
    //                     Log::error('JSON processing error: ' . $jsonError->getMessage() . $response);
    //                 }
    //             } else {
    //                 Log::error('Translation failed for data: ' . $response);
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Translation job failed for activity ID ' . $this->activityId . ': ' . $e->getMessage());
    //         throw $e; // Re-throw to ensure job failure is properly handled
    //     }
    // }


    public function handle()
    {
        try {
            $activity = Activity::find($this->activityId);

            if ($activity) {
                $activityDataArray = [];
                $translate = "";

                if ($this->lang === 'id') {
                    $activityDataArray['activity_des'] = $activity->activity_des ?? 'N/A';
                    $translate = "translation to Indonesian in JSON format without changing the structure or field that exists, only translate the content:";
                }

                // Encode data ke JSON
                $typeDataJson = json_encode($activityDataArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                // Pisahkan JSON menjadi chunks
                $chunks = $this->chunkText($typeDataJson, 4000);

                $translatedChunks = [];
                $openai = new OpenAIService();

                foreach ($chunks as $chunk) {
                    $response = $openai->getAIResponse($translate . ' ' . $chunk);

                    if ($response['status'] === false) {
                        Log::info('OpenAI failed, falling back to GeminiService');
                        $gemini = new GeminiService();
                        $response = $gemini->getAIResponse($translate . ' ' . $chunk);
                    }

                    if ($response['status']) {
                        // Bersihkan karakter kontrol dari hasil terjemahan
                        $cleanedResponse = preg_replace('/[\x00-\x1F\x7F]/u', '', $response['data']);
                        $translatedChunks[] = $cleanedResponse;
                    } else {
                        Log::error('Translation failed for chunk: ' . $chunk);
                    }
                }

                // Gabungkan hasil terjemahan
                $finalTranslation = implode(' ', $translatedChunks);

                try {
                    // Decode JSON hasil akhir
                    $decode = json_decode($finalTranslation, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('JSON decode error: ' . json_last_error_msg());
                    }

                    // Simpan hasil terjemahan
                    if ($this->lang === 'id') {
                        $activity->activity_des = $decode['activity_des'];
                    } else {
                        $activity->activity_des_id = $decode['activity_des'];
                    }

                    $activity->save();
                } catch (\Exception $jsonError) {
                    Log::error('JSON processing error: ' . $jsonError->getMessage());
                    Log::error('Failed translation data: ' . $finalTranslation);
                }
            }
        } catch (\Exception $e) {
            Log::error('Translation job failed for activity ID ' . $this->activityId . ': ' . $e->getMessage());
            throw $e; // Re-throw untuk memastikan job failure di-handle dengan baik
        }
    }
}
