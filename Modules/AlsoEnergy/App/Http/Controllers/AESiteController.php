<?php

namespace Modules\AlsoEnergy\App\Http\Controllers;

use App\Exceptions\NotFoundException;
use Modules\AlsoEnergy\App\Models\AESite;
use Modules\AlsoEnergy\Resources\AESiteResource;
use Modules\AlsoEnergy\Resources\AESiteCollection;
use Modules\AlsoEnergy\App\Http\Requests\Site\EditRequest;
use Modules\AlsoEnergy\App\Http\Requests\Site\IndexRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class AESiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function index(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10000);

        $filter = $request->only(['ae_site_id','id','plant_id']);

        $query = AESite::query();

        if(isset($filter['ae_site_id']))
        {
            $query->where('ae_site_id', $filter['ae_site_id']);
        }
        if(isset($filter['id']))
        {
            $query->where('id', $filter['id']);
        }

        if(isset($filter['plant_id']))
        {
            $query->where('plant_id', $filter['plant_id']);
        }

        $sites = $query->paginate($limit, ['*'], 'page', $page);

        return new AESiteCollection($sites);
    }
    public function get($id){
        $site = AESite::find($id);
        
        if(!$site)
        {
            throw new NotFoundException('Site not found');            
        }
       
        return new AESiteResource($site, true);
    }

    
    public function update(EditRequest $request, $id)
    {
        $site = AESite::find($id);

        if(!$site)
        {
            throw new NotFoundException('Site not found');
        }    

        // if(isset($request->plant_id))
        // {
            // $site->plant_id = $request->plant_id;
        // }   
     
        $site->save();
        
        return new AESiteResource($site);
    }

  
}
