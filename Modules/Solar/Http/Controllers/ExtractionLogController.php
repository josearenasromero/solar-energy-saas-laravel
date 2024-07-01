<?php

namespace Modules\Solar\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Exceptions\NotFoundException;
use Modules\Common\Entities\ExtractionLog;
use Modules\Solar\Http\Requests\ExtractionLogRequest\IndexRequest;
use Modules\Solar\Resources\ExtractionLogCollection;
use Modules\Solar\Resources\ExtractionLogResource;

class ExtractionLogController extends Controller
{
    public function index(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 100);

        $query = ExtractionLog::query();

        $logs = $query->paginate($limit, ['*'], 'page', $page);

        return new ExtractionLogCollection($logs, false);
    }

    public function get($id)
    {
        $log = ExtractionLog::find($id);

        if(!$log)
        {
            throw new NotFoundException('Log not found');
        }

        return new ExtractionLogResource($log);
    }

}
