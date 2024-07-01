<?php

namespace Modules\Solar\Http\Controllers;

use App\Exceptions\NotFoundException;
use Illuminate\Routing\Controller;
use Modules\QOS\Entities\Inverter;
use Modules\QOS\Http\Requests\Inverter\EditRequest;
use Modules\QOS\Http\Requests\Inverter\IndexRequest;
use Modules\QOS\Http\Requests\Inverter\MassiveRequest;
use Modules\QOS\Resources\InverterCollection;
use Modules\QOS\Resources\InverterResource;

class InverterController extends Controller
{
    public function index(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10000);

        $filter = $request->only(['active', 'company_id', 'plant_id', 'group', 'manufacturer', 'model']);

        $query = Inverter::query();

        $query->whereNot('manufacturer', 'UNKNOWN');

        if(isset($filter['active']))
        {
            $query->where('active', $filter['active']);
        }

        if(isset($filter['company_id']))
        {
            $query->whereHas('plant', function ($query) use ($filter) {
                $query->where('company_id', $filter['company_id']);
            });
        }

        if(isset($filter['plant_id']))
        {
            $query->where('plant_id', $filter['plant_id']);
        }

        if(isset($filter['group']))
        {
            $query->where('group', $filter['group']);
        }

        if(isset($filter['manufacturer']))
        {
            $query->where('manufacturer', $filter['manufacturer']);
        }

        if(isset($filter['model']))
        {
            $query->where('model', $filter['model']);
        }

        $inverters = $query->paginate($limit, ['*'], 'page', $page);

        return new InverterCollection($inverters);
    }

    public function get($id)
    {
        $inverter = Inverter::find($id);

        if(!$inverter)
        {
            throw new NotFoundException('Inverter not found');
        }

        return new InverterResource($inverter);
    }

    public function update(EditRequest $request, $id)
    {
        $inverter = Inverter::find($id);

        if(!$inverter)
        {
            throw new NotFoundException('Inverter not found');
        }

        if(isset($request->active)) 
        {
            $inverter->active = $request->active;
        }

        if(isset($request->meter_id)) 
        {
            $inverter->meter_id = $request->meter_id;
        }

        $inverter->save();

        return new InverterResource($inverter);
    }

    public function massiveUpdate(MassiveRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10000);

        $inverters = Inverter::whereIn('id', $request->ids)->paginate($limit, ['*'], 'page', $page);

        if(!$inverters)
        {
            throw new NotFoundException('Inverter not found');
        }

        if(isset($request->active)) 
        {
            foreach($inverters as $inverter)
            {
                $inverter->active = $request->active;
                $inverter->save();
            }
        }

        return new InverterCollection($inverters);
    }
}
