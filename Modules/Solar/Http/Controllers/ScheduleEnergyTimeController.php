<?php

namespace Modules\Solar\Http\Controllers;
use App\Exceptions\NotFoundException;
use Illuminate\Routing\Controller;
use Modules\RateAcuity\Entities\ScheduleEnergyTime;
use Modules\RateAcuity\Http\Requests\ScheduleEnergyTime\IndexRequest;
use Modules\RateAcuity\Resources\ScheduleEnergyTimeCollection;
use Modules\RateAcuity\Resources\ScheduleEnergyTimeResource;

class ScheduleEnergyTimeController extends Controller
{
    public function index(IndexRequest $request)
    {

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 100);

        $filter = $request->only(['schedule_id']);

        $query = ScheduleEnergyTime::query();

        if(isset($filter['schedule_id']))
        {
            $query->where('schedule_id', $request->input('schedule_id'));
        }

        $scheduleEnergyTime = $query->paginate($limit, ['*'], 'page', $page);

        return new ScheduleEnergyTimeCollection($scheduleEnergyTime);
    }

    public function get($id)
    {
        $scheduleEnergyTime = ScheduleEnergyTime::find($id);

        if(!$scheduleEnergyTime)
        {
            throw new NotFoundException('Schedule Energy Time not found');
        }

        return new ScheduleEnergyTimeResource($scheduleEnergyTime);
    }
}