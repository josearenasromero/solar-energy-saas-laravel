<?php

namespace Modules\Solar\Http\Controllers;

use App\Exceptions\NotFoundException;
use Illuminate\Routing\Controller;
use Modules\QOS\Entities\Plant;
use Modules\UtilityAPI\Http\Requests\Meter\EditRequest;
use Modules\UtilityAPI\Resources\MeterCollection;
use Modules\UtilityAPI\Entities\Meter;
use Modules\Solar\Entities\MeterPlant;
use Modules\UtilityAPI\Http\Requests\Meter\IndexRequest;
use Modules\UtilityAPI\Resources\MeterResource;
use Modules\Solar\Resources\MeterPlantResource;
use Modules\UtilityAPI\Http\Requests\Meter\DeleteRequest;

class MeterController extends Controller
{
    public function index(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10000);

        $filter = $request->only(['utilityapi_meter_id','authorization_id']);

        $query = Meter::query();

        if(isset($filter['utilityapi_meter_id']))
        {
            $query->where('utilityapi_meter_id', 'like', '%'.$filter['utilityapi_meter_id'].'%');
        }
        if(isset($filter['authorization_id']))
        {
            $query->where('authorization_id', '=', $filter['authorization_id']);
        }

        $meter = $query->paginate($limit, ['*'], 'page', $page);

        return new MeterCollection($meter);
    }

    public function get($id)
    {
        $meter = Meter::find($id);

        if(!$meter)
        {
            throw new NotFoundException('Meter not found');
        }

        return new MeterResource($meter);
    }

    public function update(EditRequest $request, $id)
    {
        $meter_plant_id = $request->input('id');

        $meter_plant = MeterPlant::find($meter_plant_id);

        if(!$meter_plant)
        {
            throw new NotFoundException('Meter not found');
        }

        if(isset($request->schedule_id))
        {
            $meter_plant->schedule_id = $request->schedule_id;
        }

        if(isset($request->is_generator))
        {
            $meter_plant->is_generator = $request->is_generator;
        }

        $meter_plant->save();

        return new MeterPlantResource($meter_plant);
    }

    public function removeMeter($id){

        $meter_plant = MeterPlant::find($id);

        if(!$meter_plant)
        {
            throw new NotFoundException('Meter not found');
        }

        $meter_plant->delete();
        return new MeterPlantResource($meter_plant);
    }
}
