<?php

namespace Modules\Solar\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Exceptions\NotFoundException;
use Modules\QOS\Entities\Company;
use Modules\QOS\Http\Requests\Company\IndexRequest;
use Modules\QOS\Http\Requests\Company\EditRequest;
use Modules\QOS\Resources\CompanyCollection;
use Modules\QOS\Resources\CompanyResource;
use Modules\Utility\Entities\Authorization;

class CompanyController extends Controller
{
    public function index(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 100);

        $filter = $request->only(['active']);

        $query = Company::query();

        if(isset($filter['active']))
        {
            $query->where('active', $request->input('active'));
        }

        $companies = $query->paginate($limit, ['*'], 'page', $page);

        return new CompanyCollection($companies, false);
    }

    public function get($id)
    {
        $company = Company::find($id);

        if(!$company)
        {
            throw new NotFoundException('Company not found');
        }

        return new CompanyResource($company);
    }


}
