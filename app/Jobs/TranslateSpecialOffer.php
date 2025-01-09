<?php

namespace App\Jobs;

use App\Models\SpecialOffer;
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

class TranslateSpecialOffer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $specialOfferId;
    protected $lang = 'id';

    /**
     * Create a new job instance.
     *
     * @param int $specialOfferId
     * @param string $lang
     */
    public function __construct(int $specialOfferId, string $lang)
    {
        $this->specialOfferId = $specialOfferId;
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
            $specialOffer = SpecialOffer::find($this->specialOfferId);

            if ($specialOffer) {
                $specialOfferDataArray = [];
                $translate = "";

                if ($this->lang === 'id') {
                    $specialOfferDataArray['title'] = $specialOffer->title ?? 'N/A';
                    $specialOfferDataArray['short_des'] = $specialOffer->short_des ?? 'N/A';
                    $specialOfferDataArray['des'] = $specialOffer->des ?? 'N/A';
                    $specialOfferDataArray['tnc'] = $specialOffer->tnc ?? 'N/A';
                    $translate = " terjemahan dalam format JSON ke Bahasa Inggris ";
                } else {
                    $specialOfferDataArray['title'] = $specialOffer->title ?? 'N/A';
                    $specialOfferDataArray['short_des'] = $specialOffer->short_des ?? 'N/A';
                    $specialOfferDataArray['des'] = $specialOffer->des ?? 'N/A';
                    $specialOfferDataArray['tnc'] = $specialOffer->tnc ?? 'N/A';
                    $translate = " terjemahan dalam format JSON ke Bahasa Indonesia ";
                }

                $typeDataJson = json_encode($specialOfferDataArray);
                sleep(10);
                $groqService = new GroqService();
                $response = $groqService->getAIResponse($translate . ' ' . $typeDataJson);

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
                            $specialOffer->title = $decoded['title'];
                            $specialOffer->short_des = $decoded['short_des'];
                            $specialOffer->des = $decoded['des'];
                            $specialOffer->tnc = $decoded['tnc'];
                        } else {
                            $specialOffer->title_id = $decoded['title'];
                            $specialOffer->short_des_id = $decoded['short_des'];
                            $specialOffer->des_id = $decoded['des'];
                            $specialOffer->tnc_id = $decoded['tnc'];
                        }
                        $specialOffer->save();
                    } catch (\Exception $jsonError) {
                        Log::error('JSON processing error: ' . $jsonError->getMessage());
                    }
                } else {
                    Log::error('Translation failed for special offer ID ' . $this->specialOfferId . ': ' . json_encode($response));
                }
            }
        } catch (\Exception $e) {
            Log::error('Translation job failed for special offer ID ' . $this->specialOfferId . ': ' . $e->getMessage());
        }
    }
}
