<?php

namespace Modules\AlsoEnergy\App\Http\Controllers;
use Modules\AlsoEnergy\App\Models\AEMeasurement;
use Modules\AlsoEnergy\App\Http\Requests\Measurement\IndexRequest;
use Modules\AlsoEnergy\Resources\AEMeasurementCollection;
use Modules\AlsoEnergy\Resources\AEMeasurementResource;
use Illuminate\Routing\Controller;
use App\Exceptions\NotFoundException;

class AEMeasurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $filter = $request->only(['ae_hardware_id','start_at','end_at']);


        $query = AEMeasurement::query();

        if(isset($filter['ae_hardware_id']))
        {
            $query->where('ae_hardware_id', $filter['ae_hardware_id']);
        }
        if(isset($filter['start_at']))
        {
            $query->where('collected_at','>=' ,$filter['start_at']);
        }
        if(isset($filter['end_at']))
        {
            $query->where('collected_at','<=' ,$filter['end_at']);
        }

        $measurements = $query->paginate($limit, ['*'], 'page', $page);

        return new AEMeasurementCollection($measurements);
    }

    public function get($id)
    {
        $measurement = AEMeasurement::find($id);

        if(!$measurement)
        {
            throw new NotFoundException('Measurement not found');
        }
        
        return new AEMeasurementResource($measurement);
    }
  

   
}
