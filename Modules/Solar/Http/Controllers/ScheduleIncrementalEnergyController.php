<?php

namespace Modules\Solar\Http\Controllers;
use App\Exceptions\NotFoundException;
use Illuminate\Routing\Controller;
use Modules\RateAcuity\Entities\ScheduleIncrementalEnergy;
use Modules\RateAcuity\Http\Requests\ScheduleEnergyTime\IndexRequest;
use Modules\RateAcuity\Resources\ScheduleIncrementalEnergyCollection;
use Modules\RateAcuity\Resources\ScheduleIncrementalEnergyResource;

class ScheduleIncrementalEnergyController extends Controller
{
    public function index(IndexRequest $request)
    {

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 100);

        $filter = $request->only(['schedule_id']);

        $query = ScheduleIncrementalEnergy::query();

        if(isset($filter['schedule_id']))
        {
            $query->where('schedule_id', $request->input('schedule_id'));
        }

        $ScheduleIncrementalEnergy = $query->paginate($limit, ['*'], 'page', $page);

        return new ScheduleIncrementalEnergyCollection($ScheduleIncrementalEnergy);
    }

    public function get($id)
    {
        $scheduleIncrementalEnergy = ScheduleIncrementalEnergy::find($id);

        if(!$scheduleIncrementalEnergy)
        {
            throw new NotFoundException('Schedule Energy Time not found');
        }

        return new ScheduleIncrementalEnergyResource($scheduleIncrementalEnergy);
    }
}