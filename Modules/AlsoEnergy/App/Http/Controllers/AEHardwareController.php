<?php

namespace Modules\AlsoEnergy\App\Http\Controllers;

use Modules\AlsoEnergy\App\Models\AEHardware;
use Modules\AlsoEnergy\App\Http\Requests\Hardware\IndexRequest;
use Modules\AlsoEnergy\Resources\AEHardwareCollection;
use Modules\AlsoEnergy\Resources\AEHardwareResource;
use Illuminate\Routing\Controller;
use App\Exceptions\NotFoundException;

class AEHardwareController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $filter = $request->only(['ae_site_id']);

        $query = AEHardware::query();

        if(isset($filter['ae_site_id']))
        {
            $query->where('ae_site_id', $filter['ae_site_id']);
        }

        $hardware = $query->paginate($limit, ['*'], 'page', $page);

        return new AEHardwareCollection($hardware);
    }
    public function get($id)
    {
        $hardware = AEHardware::find($id);

        if(!$hardware)
        {
            throw new NotFoundException('Hardware not found');
        }
       

        return new AEHardwareResource($hardware);
    }
    
}
