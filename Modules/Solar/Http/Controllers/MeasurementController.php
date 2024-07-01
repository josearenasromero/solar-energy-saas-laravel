<?php

namespace Modules\Solar\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\QOS\Entities\Measurement;
use Modules\QOS\Http\Requests\Measurement\IndexRequest;
use Modules\QOS\Resources\MeasurementCollection;
use Modules\QOS\Resources\MeasurementResource;

class MeasurementController extends Controller
{
    public function index(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $filter = $request->only(['sensor_id']);


        $query = Measurement::query();

        if(isset($filter['sensor_id']))
        {
            $query->where('sensor_id', $filter['sensor_id']);
        }

        $measurements = $query->paginate($limit, ['*'], 'page', $page);

        return new MeasurementCollection($measurements);
    }

    public function get($id)
    {
        $measurement = Measurement::find($id);

        return new MeasurementResource($measurement);
    }
}
