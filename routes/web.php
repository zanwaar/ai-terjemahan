<?php

use App\Jobs\TranslateActivity;
use App\Jobs\TranslateCategory;
use App\Jobs\TranslateDestination;
use App\Jobs\TranslateEvent;
use App\Jobs\TranslateGuide;
use App\Jobs\TranslateSpecialOffer;
use App\Jobs\TranslateType;
use App\Models\Guide;
use App\Models\SpecialOffer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/translate/activity', function () {
    try {
        // Retrieve activities (you can limit or paginate the results)
        // $activities = \App\Models\Activity::where('slug', '5h4m-bali')->get();
        // $activities = \App\Models\Activity::whereIn('id', [179])->get();
        // $activities = \App\Models\Activity::select(['id', 'name_id'])->get();
        $activities = \App\Models\Activity::get();


        if ($activities->isEmpty()) {
            return response()->json(['error' => 'No activities found'], 404);
        }
        $activityData = [];

        foreach ($activities as $activity) {

            if ($activity->activity_des === $activity->activity_des_id) {
                $activityData[] = [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'name_id' => $activity->name_id,
                    // 'short_des' => $activity->short_des,
                    // 'short_des_id' => $activity->short_des_id,
                    'activity_des' => $activity->activity_des,
                    'activity_des_id' => $activity->activity_des_id,
                    // 'included' => $activity->included,
                    // 'included_id' => $activity->included_id,
                    // 'whattobring' => $activity->whattobring,
                    // 'whattobring_id' => $activity->whattobring_id,
                    // 'note' => $activity->note,
                    // 'note_id' => $activity->note_id,
                    // 'highlight' => $activity->highlight,
                    // 'highlight_id' => $activity->highlight_id,
                    // 'pickup_time' => $activity->pickup_time,
                    // 'pickup_time_id' => $activity->pickup_time_id,
                    // 'confirmation_detail' => $activity->confirmation_detail,
                    // 'confirmation_detail_id' => $activity->confirmation_detail_id,
                    // 'how_to_use' => $activity->how_to_use,
                    // 'how_to_use_id' => $activity->how_to_use_id,
                    // 'payment' => $activity->payment,
                    // 'payment_id' => $activity->payment_id

                ];
                // TranslateActivity::dispatch($activity->id, 'id');
            }
        }

        // TranslateActivity::dispatch($activities, 'id');

        // Dispatch the job for each activity
        // foreach ($activities as $activity) {
        //     TranslateActivity::dispatch($activity->id, 'en');
        // }

        return response()->json(['count' =>  count($activityData), 'data' => $activityData]);
        // return response()->json(['message' => 'Translation jobs have been queued successfully', 'count' => $activities->count(), 'data' => $activities]);
    } catch (\Exception $e) {
        Log::error('Queue job dispatch failed: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while dispatching the job'], 500);
    }
});
Route::get('/translate/destination', function () {
    try {
        // Retrieve destinations (you can limit or paginate the results)
        $destinations = \App\Models\Destination::get();  // Fetch 2 destinations for this example
        $destinationsData = [];

        foreach ($destinations as $destination) {

            if ($destination->des === $destination->des_id) {

                $destinationsData[] = [
                    'id' => $destination->id,
                    'name_id' => $destination->name_id,
                    'des_id' => $destination->des_id,
                    'note_id' => $destination->note_id,
                    'name' => $destination->name,
                    'des' => $destination->des,
                    'note' => $destination->note,
                ];

                // TranslateDestination::dispatch($destination->id, 'en');
            }
        }


        if ($destinations->isEmpty()) {
            return response()->json(['error' => 'No destinations found'], 404);
        }

        // Dispatch the job for each destination
        // foreach ($destinations as $destination) {
        //     TranslateDestination::dispatch($destination->id, 'id');
        // }

        return response()->json(['count' =>  count($destinationsData), 'data' => $destinationsData]);
        // return response()->json(['message' => 'Translation jobs have been queued successfully', 'count' => $destinations->count(), 'data' => $destinations]);
    } catch (\Exception $e) {
        Log::error('Queue job dispatch failed: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while dispatching the job'], 500);
    }
});

Route::get('/translate/event', function () {
    try {
        // Retrieve events (you can limit or paginate the results)
        $events = \App\Models\Event::where('name_id', ' ')->get();  // Fetch 2 events for this example

        if ($events->isEmpty()) {
            return response()->json(['error' => 'No events found'], 404);
        }
        // $eventssData = [];

        foreach ($events as $events) {

            if ($events->name === $events->name_id) {

                $eventssData[] = [
                    'id' => $events->id,
                    'name_id' => $events->name_id,
                    'des_id' => $events->des_id,
                    'note_id' => $events->note_id,
                    'name' => $events->name,
                    'des' => $events->des,
                    'note' => $events->note,
                ];

                // TranslateEvent::dispatch($event->id, 'id');
            }
        }
        // return response()->json(['count' =>  count($eventssData), 'data' => $eventssData]);
        return response()->json(['message' => 'Translation jobs have been queued successfully', 'data' => $events]);
    } catch (\Exception $e) {
        Log::error('Queue job dispatch failed: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while dispatching the job'], 500);
    }
});

Route::get('/translate/guide', function () {
    try {
        // Retrieve guides (you can limit or paginate the results)
        $guides = \App\Models\Guide::get();  // Fetch 2 guides for this example
        // $guides = Guide::where('id', 10)->get();
        $guidedata = [];

        if ($guides->isEmpty()) {
            return response()->json(['error' => 'No guides found'], 404);
        }

        foreach ($guides as $guide) {

            if ($guide->title == $guide->title_id) {
                $guidedata[] = [
                    'id' => $guide->id,
                    'title_id' => $guide->title_id,
                    'content_id' => $guide->content_id,
                    'title' => $guide->title,
                    'content' => $guide->content,

                ];

                // TranslateGuide::dispatch($guide->id, 'en');
            }
        }

        return response()->json(['count' =>  count($guidedata), 'data' => $guidedata]);
        // return response()->json(['message' => 'Translation jobs have been queued successfully', 'count' => $guides->count(), 'data' => $guides , 'data' => $guidedata]);
    } catch (\Exception $e) {
        Log::error('Queue job dispatch failed: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while dispatching the job'], 500);
    }
});

Route::get('/translate/special-offer', function () {
    try {
        // Retrieve special offers (you can limit or paginate the results)
        $specialOffers = SpecialOffer::get();  // Fetch 2 special offers for this example
        // $specialOffers = SpecialOffer::where('id', 21)->get();
        $specialOfferData = [];

        if ($specialOffers->isEmpty()) {
            return response()->json(['error' => 'No special offers found'], 404);
        }
        foreach ($specialOffers as $specialOffer) {

            if ($specialOffer->title_id == $specialOffer->title) {
                $specialOfferData[] =   [
                    'id' => $specialOffer->id,
                    'title_id' => $specialOffer->title_id,
                    'short_des_id' => $specialOffer->short_des_id,
                    'des_id' => $specialOffer->des_id,
                    'tnc_id' => $specialOffer->tnc_id,
                    'title' => $specialOffer->title,
                    'short_des' => $specialOffer->short_des,
                    'des' => $specialOffer->des,
                    'tnc' => $specialOffer->tnc,

                ];
            }



            // TranslateSpecialOffer::dispatch($specialOffer->id, 'id');
        }
        return response()->json(['count' =>  count($specialOfferData), 'data' => $specialOfferData]);
        // return response()->json(['message' => 'Translation jobs have been queued successfully', 'data' => $specialOffers]);
    } catch (\Exception $e) {
        Log::error('Queue job dispatch failed: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while dispatching the job'], 500);
    }
});

Route::get('/translate/type', function () {
    try {
        // Retrieve types (you can limit or paginate the results)
        // $types = Type::where('id', 8)->get();  // Fetch 2 types for this example
        $types = App\Models\Type::get();
        // $types = \App\Models\Type::get();  // Fetch 2 types for this example
        $typesData = []; // Fetch 2 types for this example

        if ($types->isEmpty()) {
            return response()->json(['error' => 'No types found'], 404);
        }

        // Dispatch the job for each type
        foreach ($types as $type) {

            if ($type->name == $type->name_id) {
                $typesData[] = [
                    'id' => $type->id,
                    'name_id' => $type->name_id,
                    'title_id' => $type->title_id,
                    'des_id' => $type->des_id,
                    'name' => $type->name,
                    'title' => $type->title,
                    'des' => $type->des,
                ];
            }

            // TranslateType::dispatch($type->id, 'en');
        }
        return response()->json(['count' =>  count($typesData), 'data' => $typesData]);
        // return response()->json(['message' => 'Translation jobs have been queued successfully', 'data' => $types]);
    } catch (\Exception $e) {
        Log::error('Queue job dispatch failed: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while dispatching the job'], 500);
    }
});
