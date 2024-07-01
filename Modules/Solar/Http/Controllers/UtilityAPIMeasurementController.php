<?php

namespace Modules\Solar\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\UtilityAPI\Entities\UtilityMeasurement as Measurement;
use Modules\UtilityAPI\Http\Requests\Measurement\IndexRequest;
use Modules\UtilityAPI\Resources\MeasurementCollection;
use Modules\UtilityAPI\Resources\MeasurementResource;

class UtilityAPIMeasurementController extends Controller
{
    public function index(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $filter = $request->only(['meter_id']);


        $query = Measurement::query();

        if(isset($filter['meter_id']))
        {
            $query->where('meter_id', $filter['meter_id']);
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
