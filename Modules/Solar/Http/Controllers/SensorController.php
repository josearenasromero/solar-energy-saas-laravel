<?php

namespace Modules\Solar\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\QOS\Entities\Sensor;
use Modules\QOS\Http\Requests\Sensor\IndexRequest;
use Modules\QOS\Resources\SensorCollection;
use Modules\QOS\Resources\SensorResource;

class SensorController extends Controller
{
    public function index(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $filter = $request->only(['inverter_id']);

        $query = Sensor::query();

        if(isset($filter['inverter_id']))
        {
            $query->where('inverter_id', $filter['inverter_id']);
        }

        $sensors = $query->paginate($limit, ['*'], 'page', $page);

        return new SensorCollection($sensors);
    }

    public function get($id)
    {
        $sensor = Sensor::find($id);

        return new SensorResource($sensor);
    }
}
