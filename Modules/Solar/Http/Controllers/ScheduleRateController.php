<?php

namespace Modules\Solar\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\RateAcuity\Entities\ScheduleRate;
use Modules\RateAcuity\Http\Requests\ScheduleRate\IndexRequest;
use Modules\RateAcuity\Resources\ScheduleRateCollection;
use Modules\RateAcuity\Resources\ScheduleRateResource;

class ScheduleRateController extends Controller
{
    public function index(IndexRequest $request){

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 1000);

        $filter = $request->only(['schedule_id']);

        $query = ScheduleRate::query();

        if(isset($filter['schedule_id'])){
            $query->where('schedule_id', $filter['schedule_id']);
        }

        $schedule = $query->paginate($limit, ['*'], 'page', $page);

        return new ScheduleRateCollection($schedule, false);
    }

    public function get($id)
    {
        $schedule = ScheduleRate::find($id);

        return new ScheduleRateResource($schedule);
    }
}
