<?php

namespace Modules\Solar\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\RateAcuity\Entities\Utility;
use Modules\RateAcuity\Http\Requests\Utility\IndexRequest;
use Modules\RateAcuity\Resources\UtilityCollection;
use Modules\RateAcuity\Resources\UtilityResource;

class UtilityController extends Controller
{
    public function index(IndexRequest $request){

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10000);

        $filter = $request->only(['state']);

        $query = Utility::query();

        if(isset($filter['state'])){
            $query->where('state', $filter['state']);
        }

        $utility = $query->paginate($limit, ['*'], 'page', $page);

        return new UtilityCollection($utility, false);
    }

    public function get($id)
    {
        $utility = Utility::find($id);

        return new UtilityResource($utility);
    }
}
