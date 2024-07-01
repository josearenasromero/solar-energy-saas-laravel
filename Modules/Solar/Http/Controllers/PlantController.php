<?php

namespace Modules\Solar\Http\Controllers;

use App\Exceptions\NotFoundException;
use Illuminate\Routing\Controller;
use Modules\QOS\Entities\Plant;
use Modules\QOS\Http\Requests\Plant\EditRequest;
use Modules\QOS\Http\Requests\Plant\IndexRequest;
use Modules\QOS\Resources\PlantCollection;
use Modules\QOS\Resources\PlantResource;
use Modules\UtilityAPI\Entities\Meter;

class PlantController extends Controller
{
    public function index(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $filter = $request->only(['company_id']);

        $query = Plant::query();
        if(isset($filter['company_id'])){
            $query->where('company_id',$filter['company_id']);
        }
        $plants = $query->paginate($limit, ['*'], 'page', $page);

        return new PlantCollection($plants, false);
    }

    public function get($id)
    {
        $plant = Plant::find($id);
        if(!$plant)
        {
            throw new NotFoundException('Plant not found');
        }

        return new PlantResource($plant, true);
    }
    public function update(EditRequest $request, $id)
    {
        $plant = Plant::find($id);

        if(!$plant)
        {
            throw new NotFoundException('Plant not found');
        }

        if(isset($request->active))
        {
            $plant->active = $request->active;
        }

        if(isset($request->authorization_id))
        {
            $plant->authorization_id = $request->authorization_id;
        }

        if(isset($request->utility_id))
        {
            $plant->utility_id = $request->utility_id;
        }

        // if(isset($request->schedulerate_id))
        // {
        //     $plant->scheduleRate()->syncWithoutDetaching($request->schedulerate_id);
        // }

        if (isset($request->ae_site_id)) {
            $plant->ae_site_id = $request->ae_site_id;
        }

        $status_meters = '';
        $status_sid = '';
        if(isset($request->meter_id)){
            $meters = Meter::whereIn('meter_numbers',$request->meter_id)->where('authorization_id', $plant->authorization_id)->get();
            if(!$meters->isEmpty()){
                $plant->meter()->syncWithoutDetaching($meters->pluck('id'));
                $total = count($meters);
                $status_meters = $total . " Meters Added Succesfully.";
            }
            $meters_sid = Meter::whereIn('service_identifier', $request->meter_id)->where('authorization_id', $plant->authorization_id)->get();
            if(!$meters_sid->isEmpty()){
                $plant->meter()->syncWithoutDetaching($meters_sid->pluck('id'));
                $total = count($meters_sid);
                $status_sid = " " . $total . " Service Identifiers Added Succesfully.";
            }
        }

        $plant->save();
        $plant['status'] = $status_meters . $status_sid;
        return new PlantResource($plant);
    }
}
