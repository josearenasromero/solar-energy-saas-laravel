<?php

namespace Modules\Solar\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\UtilityAPI\Entities\Authorization;
use Modules\UtilityAPI\Http\Requests\Utility\IndexRequest;
use Modules\UtilityAPI\Resources\AuthorizationCollection;
use Modules\UtilityAPI\Resources\AuthorizationResource;
use Modules\UtilityAPI\Resources\UtilityCompanyCollection;

class AuthorizationController extends Controller
{
    public function index(IndexRequest $request){

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 1000);

        $filter = $request->only(['search']);

        $query = Authorization::query();

        if(isset($filter['search'])){
            $query->where('utilityapi_id','like', '%'.$filter['search'].'%  ');
            $query->Orwhere('id', 'like', '%'.$filter['search'].'%');
        }

        $utility = $query->paginate($limit, ['*'], 'page', $page);

        return new UtilityCompanyCollection($utility, false);
    }

    public function get($id)
    {
        $authorization = Authorization::find($id);

        return new AuthorizationResource($authorization);
    }
}
