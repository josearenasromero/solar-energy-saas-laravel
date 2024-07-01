<?php

namespace Modules\QOS\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\QOS\Entities\QOSCompany;
use Modules\QOS\Entities\Inverter;
use Modules\QOS\Entities\Sensor;
use Modules\QOS\Entities\QOSMeasurement;
use Modules\QOS\Http\Requests\CompaniesRequest;
use Modules\QOS\Http\Requests\InvertersRequest;
use Modules\QOS\Http\Requests\SensorsRequest;
use Modules\QOS\Http\Requests\MeasurementsRequest;
use Modules\QOS\Resources\CompaniesResource;
use Modules\QOS\Resources\InvertersResource;
use Modules\QOS\Resources\SensorsResource;
use Modules\QOS\Resources\MeasurementsResource;

class QOSController extends Controller
{
    public function list_companies(CompaniesRequest $request)
    {
        $companies = QOSCompany::all();

        return new CompaniesResource((object) ['companies' => $companies]);
    }

    public function list_inverters(InvertersRequest $request)
    {
        $inverters = DB::table('qos_inverters')
            ->join('qos_companies', 'qos_inverters.company_id', '=', 'qos_companies.id')
            ->select('qos_inverters.id', 'qos_companies.name')
            ->get();

        return new InvertersResource((object) ['inverters' => $inverters]);
    }

    public function list_sensors(SensorsRequest $request)
    {
        $sensors = Sensor::all();

        return new SensorsResource((object) ['sensors' => $sensors]);
    }

    public function list_measurements(MeasurementsRequest $request)
    {
        $measurements = QOSMeasurement::all();

        return new MeasurementsResource((object) ['measurements' => $measurements]);
    }
}
