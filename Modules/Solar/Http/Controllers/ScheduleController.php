<?php

namespace Modules\Solar\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\QOS\Entities\Plant;
use Modules\RateAcuity\Entities\Schedule;
use Modules\RateAcuity\Http\Requests\Schedule\IndexRequest;
use Modules\RateAcuity\Resources\ScheduleCollection;
use Modules\RateAcuity\Resources\ScheduleResource;

class ScheduleController extends Controller
{
    public function index(IndexRequest $request){

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        // $paginated = $request->input('paginated', true);
        $filter = $request->only(['utility_id', 'plant_id']);
        $query = Schedule::query();

        if(isset($filter['plant_id'])){
            $utility_id = Plant::find($filter['plant_id'])->utility_id;
        }
        if(isset($filter['utility_id'])){
            $utility_id = $filter['utility_id'];
        }
        if(isset($utility_id)){
            $query->where('utility_id',$utility_id);
        }
        $query->orderBy('schedule_name', 'ASC');
        $query->orderBy('option_type', 'ASC');
        $schedule = $query->get();
        return new ScheduleCollection($schedule, false);
    }

    public function get($id)
    {
        $schedule = Schedule::find($id);

        return new ScheduleResource($schedule);
    }
}
