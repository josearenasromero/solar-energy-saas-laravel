<?php

namespace Modules\Solar\Http\Controllers;

use App\Exceptions\NotFoundException;
use Illuminate\Routing\Controller;
use Modules\Solar\Entities\MeterPlant;
use Illuminate\Support\Facades\DB;
use Modules\QOS\Entities\Plant;
use Modules\RateAcuity\Entities\ScheduleDemandTime;
use Modules\RateAcuity\Entities\ScheduleEnergyTime;
use App\Exceptions\BadRequestException;
use Modules\Solar\Http\Requests\StatementRequest\IndexRequest;
use Modules\Solar\Resources\StatementCollection;
use Modules\RateAcuity\Entities\ScheduleServiceCharge;
use Modules\UtilityAPI\Entities\Meter;

class StatementController extends Controller
{
    private function find_energy_time_by_date($input_date, $energy_time_dates)
    {
        $day_of_week = (int) $input_date->format('N');
        foreach ($energy_time_dates as $energy_time_date) {
            $start_date = $energy_time_date->start_date;
            $end_date = $energy_time_date->end_date;

            $day_match = isset(($energy_time_date->days)[$day_of_week]) ? ($energy_time_date->days)[$day_of_week] : false;
            if ($input_date >= $start_date && $input_date <= $end_date && $day_match) {
                return $energy_time_date; // Return the matching date range
            }
        }

        return null; // Return null if no match is found
    }
    
    public function index(IndexRequest $request)
    {

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $is_ae = $request->input('is_ae', true);

        $filter = $request->only(['company_id', 'start_date', 'end_date', 'plant_id']);

        if(!isset($filter['start_date']) || !isset($filter['end_date'])) {
            return new StatementCollection([]);
        }

        $start_date = new \DateTime($filter['start_date']);
        $end_date = new \DateTime($filter['end_date']);
        $plant_id = isset($filter['plant_id']) ? $filter['plant_id'] : null;

        if(null === $plant_id) {
            throw new BadRequestException('Plant id is required');
        }

        //format by year-month YYYY-MM
        if ($end_date < $start_date) {
            throw new BadRequestException('End date cannot be lower than start date');
        }

        $from = $filter['start_date'];
        $to = $filter['end_date'];

        $diff = $end_date->diff($start_date);
        $years = [];
        for ($i = 0; $i <= $diff->y; $i++) {
            $years[] = (int) $start_date->format('Y') + $i;
        }

        //Get all MeterPlant data based on plant_id
        $meter_plant = MeterPlant::where('plant_id', $filter['plant_id'])->get();

        $all_energy_time = [];
        $all_demand_time = [];
        $all_service_charge_rate = [];

        $energy_time_dates = [];
        $energy_demand_dates = [];
        foreach($meter_plant as $mp) {
            $current_schedule_id = $mp->schedule_id;
            $current_meter_id = $mp->meter_id;
            $is_generator = $mp->is_generator;

            //get all entries for the specific schedule just once
            if(!isset($all_energy_time[$current_schedule_id])) {
                $all_energy_time[$current_schedule_id] = ScheduleEnergyTime::where('schedule_id', '=', $current_schedule_id)->get();
            }

            if(!isset($all_demand_time[$current_schedule_id])) {
                $all_demand_time[$current_schedule_id] = ScheduleDemandTime::where('schedule_id', '=', $current_schedule_id)->get();
            }

            if(!isset($all_service_charge_rate[$current_schedule_id])) {
                $all_service_charge_rate[$current_schedule_id] = ScheduleServiceCharge::where('schedule_id', '=', $current_schedule_id)->first();
            }

            //just so we dont get them again
            if(isset($energy_time_dates[$current_schedule_id]) || isset($energy_demand_dates[$current_schedule_id])) {
                continue;
            }

            foreach ($years as $year) {
                foreach ($all_energy_time[$current_schedule_id] as $current_energy_time) {
                    $sd = $current_energy_time->start_date;
                    $ed = $current_energy_time->end_date;
                    if (empty($sd) || empty($ed))
                        continue;
                    // we are going to format the dates correctly, since start_date can be >= end_date
                    $energy_current_year = $year;
                    $energy_start_date = \DateTime::createFromFormat('mdY', $sd . $energy_current_year);
                    $energy_end_date = \DateTime::createFromFormat('mdY', $ed . $energy_current_year);
    
                    //here we fix the date if needed
                    if ($energy_start_date > $energy_end_date) {
                        $energy_end_date = \DateTime::createFromFormat('mdY', $ed . ((int) $energy_current_year + 1));
                    }
    
                    $day_app_desc = (strlen($current_energy_time->day_app_desc) === 8) ? $current_energy_time->day_app_desc : 'YYYYYYYY'; //default value, maybe wrong?
    
                    $energy_time_date = (object) [
                        'start_date' => $energy_start_date,
                        'end_date' => $energy_end_date,
                        'season' => $current_energy_time->season,
                        'time_of_day' => $current_energy_time->time_of_day,
                        'day_app_desc' => $current_energy_time->day_app_desc,
                        'rate_kwh' => $current_energy_time->rate_kwh,
                        'days' => [
                                1 => $day_app_desc[0] === 'Y',
                                2 => $day_app_desc[1] === 'Y',
                                3 => $day_app_desc[2] === 'Y',
                                4 => $day_app_desc[3] === 'Y',
                                5 => $day_app_desc[4] === 'Y',
                                6 => $day_app_desc[5] === 'Y',
                                7 => $day_app_desc[6] === 'Y',
                                8 => $day_app_desc[7] === 'Y',
                            ]
                    ];

                    if(!isset($energy_time_dates[$current_schedule_id])) {
                        $energy_time_dates[$current_schedule_id] = [];
                    }
    
                    $energy_time_dates[$current_schedule_id][] = $energy_time_date;
                }
            }

            foreach ($years as $year) {
                foreach ($all_demand_time[$current_schedule_id] as $current_demand_time) {
                    $sd = $current_demand_time->start_date;
                    $ed = $current_demand_time->end_date;
                    if (empty($sd) || empty($ed))
                        continue;
                    // we are going to format the dates correctly, since start_date can be >= end_date
                    $demand_current_year = $year;
                    $demand_start_date = \DateTime::createFromFormat('mdY', $sd . $demand_current_year);
                    $demand_end_date = \DateTime::createFromFormat('mdY', $ed . $demand_current_year);

                    //here we fix the date if needed
                    if ($demand_start_date > $demand_end_date) {
                        $demand_end_date = \DateTime::createFromFormat('mdY', $ed . (int) ($demand_current_year + 1));
                    }

                    $day_app_desc = (strlen($current_demand_time->day_app_desc) === 8) ? $current_demand_time->day_app_desc : 'YYYYYYYY'; //default value, maybe wrong?

                    $demand_time_date = (object) [
                        'id' => $current_demand_time->id,
                        'start_date' => $demand_start_date,
                        'end_date' => $demand_end_date,
                        'season' => $current_demand_time->season,
                        'time_of_day' => $current_demand_time->time_of_day,
                        'day_app_desc' => $current_demand_time->day_app_desc,
                        'rate_kwh' => $current_demand_time->rate_kw,
                        'days' => [
                                1 => $day_app_desc[0] === 'Y',
                                2 => $day_app_desc[1] === 'Y',
                                3 => $day_app_desc[2] === 'Y',
                                4 => $day_app_desc[3] === 'Y',
                                5 => $day_app_desc[4] === 'Y',
                                6 => $day_app_desc[5] === 'Y',
                                7 => $day_app_desc[6] === 'Y',
                                8 => $day_app_desc[7] === 'Y',
                            ]
                    ];

                    if(!isset($energy_demand_dates[$current_schedule_id])) {
                        $energy_demand_dates[$current_schedule_id] = [];
                    }

                    $energy_demand_dates[$current_schedule_id][] = $demand_time_date;
                }
            }

        }

        //From $start_date to $end_date, iterate by day
        $interval = new \DateInterval('P1D');
        $date_range = new \DatePeriod($start_date, $interval, $end_date);

        $all_meters = [];
        $utilityapi_measurements_daily = [];
        $meter_grid_energy = [];
        foreach($meter_plant as $mp) {

            $current_meter = $mp->meter_id;
            $current_plant = $mp->plant_id;
            $current_schedule = $mp->schedule_id;

            //throw error indicading that the schedule is not set
            if(!isset($all_service_charge_rate[$current_schedule_id])) {
                throw new BadRequestException('Service charge rate not set for this schedule: ' . $current_schedule_id);
            }

            $plant = Plant::find($plant_id);

            if (null === $plant) {
                throw new BadRequestException('Invalid plant');
            }

            $meter_data[$current_meter] = [];

            $max_nc[$current_meter] = [];
            $max_on = [];

            $seasons[$current_meter] = [];

            $value_on_peak[$current_meter] = 0;
            $value_part_peak[$current_meter] = 0;
            $value_off_peak[$current_meter] = 0;

            $value_peak_with_rate[$current_meter] = [
                'on_peak' => 0,
                'part_peak' => 0,
                'off_peak' => 0,
            ];
            
            $demand_rate_value[$current_meter] = 0.0;
            
            foreach($date_range as $current_date) {
                $current_complete_date = $current_date->format('Y-m-d');
    
                $energy_time = null;
                $energy_demand = null;

                if(isset($energy_time_dates[$current_schedule])) {
                    $energy_time = $this->find_energy_time_by_date($current_date, $energy_time_dates[$current_schedule]);
                }

                if(isset($energy_demand_dates[$current_schedule])) {
                    $energy_demand = $this->find_energy_time_by_date($current_date, $energy_demand_dates[$current_schedule]);
                }

                if (null === $energy_time) {
                    $energy_time = (object) [
                        'time_of_day' => 'Peak',
                        'rate_kwh' => 0.0,
                    ];
                }

                if (null === $energy_demand) {
                    $energy_demand = (object) [
                        'rate_kwh' => 0.0,
                    ];
                }

                $current_complete_date_string = $current_complete_date;

                $ae_measurements = null;
                
                if($is_ae && $is_generator) {
                    $ae_measurements = DB::table('ae_measurement')
                        ->join('ae_hardware', 'ae_hardware.id', '=', 'ae_measurement.ae_hardware_id')
                        ->join('ae_site', 'ae_site.id', '=', 'ae_hardware.ae_site_id')
                        ->leftJoin('qos_plant', 'qos_plant.ae_site_id', '=', 'ae_site.id')
                        ->where('qos_plant.id', '=', $current_plant)
                        ->where('ae_measurement.value', '>', 0)
                        ->whereBetween('ae_measurement.collected_at', [$current_complete_date_string . ' 00:00:00', $current_complete_date_string . ' 23:59:59'])
                        ->orderBy('ae_measurement.collected_at', 'ASC')
                        ->get([
                            'ae_hardware.id',
                            'ae_measurement.collected_at',
                            'ae_measurement.value',
                        ]);
                }

                if(!isset($utilityapi_measurements_daily[$current_complete_date])) {
                    $meters_id = $plant->meter->pluck('id');
                    $utilityapi_measurements_daily[$current_complete_date] = 0.0;

                    $utilityapi_measurements = DB::table('utilityapi_measurements')
                        ->whereBetween('end_at', [$current_complete_date_string . ' 00:00:00', $current_complete_date_string . ' 23:59:59'])
                        ->whereIn('meter_id', $meters_id)
                        ->orderBy('utilityapi_measurements.end_at', 'ASC')
                        ->get([
                            'meter_id',
                            'end_at',
                            'kwh_value',
                            'datapoints'
                        ]);

                    if (!$utilityapi_measurements->isEmpty()) {
                        $kwh = [];
                        foreach ($utilityapi_measurements as $utilityapi_measurement) {
                            if (array_key_exists($utilityapi_measurement->meter_id, $meter_grid_energy)) {
                                $meter_grid_energy[$utilityapi_measurement->meter_id] += $utilityapi_measurement->kwh_value;
                            } else {
                                $meter_grid_energy[$utilityapi_measurement->meter_id] = $utilityapi_measurement->kwh_value;
                            }

                            $types = array_column(json_decode($utilityapi_measurement->datapoints), 'type');
                            if (in_array('fwd', $types)) {
                                foreach (json_decode($utilityapi_measurement->datapoints) as $value) {
                                    if ($value->type == 'fwd') {
                                        $kwh['fwd'][] = $value->value;
                                    }
                                }
                            } else {
                                $kwh['net'][] = $utilityapi_measurement->kwh_value;
                            }
                        }

                        $utilityapi_measurements_daily[$current_complete_date] = array_sum($meter_grid_energy);
                        if(!isset($utilityapi_measurements_daily_meter[$current_meter])) {
                            $utilityapi_measurements_daily_meter[$current_meter] = 0.0;
                        }
                        $utilityapi_measurements_daily_meter[$current_meter] = isset($meter_grid_energy[$current_meter]) ? $meter_grid_energy[$current_meter] : 0.0;
                    }
                }

                //$target_meter_percentage = isset($meter_grid_energy[$current_meter]) ? round($meter_grid_energy[$current_meter] / $utilityapi_measurements_daily[$current_complete_date], 4) : 0.0;

                if(!isset($meter_energy_cost[$current_complete_date])) {
                    $meter_energy_cost[$current_complete_date] = 0.0;
                }

                if(!isset($meter_energy_cost_solar[$current_complete_date])) {
                    $meter_energy_cost_solar[$current_complete_date] = 0.0;
                }

                if(!isset($meter_energy_cost_meter[$current_meter][$current_complete_date])) {
                    $meter_energy_cost_meter[$current_meter][$current_complete_date] = 0.0;
                }

                if(!isset($meter_energy_cost_solar_meter[$current_meter][$current_complete_date])) {
                    $meter_energy_cost_solar_meter[$current_meter][$current_complete_date] = 0.0;
                }

                $meter_energy_cost_solar[$current_complete_date] = $meter_energy_cost_solar[$current_complete_date] + ($meter_grid_energy[$current_meter]) * $all_service_charge_rate[$current_schedule_id]->rate;
                $meter_energy_cost_solar_meter[$current_meter][$current_complete_date] = $meter_energy_cost_solar_meter[$current_meter][$current_complete_date] + ($meter_grid_energy[$current_meter]) * $all_service_charge_rate[$current_schedule_id]->rate;
                
                //now the same for AE
                if (null !== $ae_measurements && !$ae_measurements->isEmpty()) {
                    $inverters = [];
                    $inverters_date = [];
                    $filtered_inverters = array_unique($ae_measurements->toArray(), SORT_REGULAR);

                    foreach ($filtered_inverters as $inv) {
                        $inverters_date[$inv->id][] = $inv->collected_at;
                    }

                    foreach ($filtered_inverters as $data) {
                        $inv_from = reset($inverters_date[$data->id]);
                        $inv_to = end($inverters_date[$data->id]);

                        //here we may sum by entry, but we group it instead and get the first and last reading form the day
                        if ($data->collected_at == $inv_from) {
                            $inverters['diff'][] = $data->value;
                        }
                        if ($data->collected_at == $inv_to) {
                            $inverters['sum'][] = $data->value;
                        }
                    }

                    if (count($inverters['diff']) === count($inverters['sum'])) {
                        for ($k = 0; $k < count($inverters['diff']); $k++) {
                            //$max_nc[] = round((($inverters['sum'][$k] - $inverters['diff'][$k])/1000), 4);
                            $max_nc[$current_meter][] = round((($inverters['sum'][$k] - $inverters['diff'][$k])), 4);
                        }
                    }

                    $value_total = round(((array_sum($inverters['sum']) - array_sum($inverters['diff']))), 4);
                    //$value_total_ponderated = round(((array_sum($inverters['sum']) - array_sum($inverters['diff']))), 4) * $target_meter_percentage;
                    $value_total_ponderated = 0;

                    $meter_energy_cost[$current_complete_date] = $meter_energy_cost[$current_complete_date] + ($value_total_ponderated + $meter_grid_energy[$current_meter]) * $all_service_charge_rate[$current_schedule_id]->rate;
                    $meter_energy_cost_meter[$current_meter][$current_complete_date] = $meter_energy_cost_meter[$current_meter][$current_complete_date] + ($value_total_ponderated + $meter_grid_energy[$current_meter]) * $all_service_charge_rate[$current_schedule_id]->rate;

                    $time_of_day = trim($energy_time->time_of_day);

                    $energy_rate = empty($energy_time->rate_kwh) ? 0.0 : (float) $energy_time->rate_kwh;
                    $demand_rate = empty($energy_demand->rate_kwh) ? 0.0 : (float) $energy_demand->rate_kwh;

                    if ($time_of_day == 'On-Peak' || $time_of_day == 'Peak') {
                        $value_on_peak[$current_meter] += $value_total;
                        $value_peak_with_rate[$current_meter]['on_peak'] += (float) $energy_rate * $value_total;
                        $demand_rate_value[$current_meter] += $demand_rate * $value_total; //note that will only consider time_of_day based on energy_time
                    }
                    if ($time_of_day == 'Off-Peak') {
                        $value_off_peak[$current_meter] += $value_total;
                        $value_peak_with_rate[$current_meter]['off_peak'] += $energy_rate * $value_total;
                        $demand_rate_value[$current_meter] += $demand_rate * $value_total;
                    }
                    if ($time_of_day == 'Part-Peak' || $time_of_day == 'Mid-Peak') {
                        $value_part_peak[$current_meter] += $value_total;
                        $value_peak_with_rate[$current_meter]['part_peak'] += $energy_rate * $value_total;
                        $demand_rate_value[$current_meter] += $demand_rate * $value_total;
                    }
                }

                //here we are at the end of the day
            }
            //here we are out of dayly loop
        }

        $plants = [];

        foreach($meter_plant as $mp) {
            $value_final_meter = 0.0; //AKA Solar Usage (kWh)
            $utility_final_meter = 0.0;
            $total_usage_final_meter = 0.0; //AKA Total Usage (kWh)
            $bill_w_o_solar_meter = 0.0; //AKA Bill w/o Solar ($)

            $current_meter = $mp->meter_id;
            $meter = Meter::find($current_meter);
            $value_final_meter = $value_on_peak[$current_meter] + $value_part_peak[$current_meter] + $value_off_peak[$current_meter];
            $utility_final_meter = isset($utilityapi_measurements_daily_meter[$current_meter]) ? $utilityapi_measurements_daily_meter[$current_meter] : 0.0;
            $total_usage_final_meter = $value_final_meter + $utility_final_meter;
            $bill_w_o_solar_meter = array_sum($meter_energy_cost_meter[$current_meter]);
            $bill_w_solar_meter = array_sum($meter_energy_cost_solar_meter[$current_meter]);

            $c_plant = new \StdClass;
            $c_plant->id = $current_meter;
            $c_plant->meter = $meter->meter_numbers;
            $c_plant->usage_detail = $usage_detail ?? [];
            $c_plant->total_usage = number_format(round($total_usage_final_meter / 1000, 2), 2, '.', ',');
            $c_plant->net_usage = number_format(round($value_final_meter / 1000, 2), 2, '.', ',');
            $c_plant->bill_w_o_solar = number_format(round($bill_w_o_solar_meter / 1000, 2), 2, '.', ',');
            $c_plant->bill_w_solar = number_format(round($bill_w_solar_meter / 1000, 2), 2, '.', ',');
            $c_plant->savings = number_format(round(($bill_w_o_solar_meter - $bill_w_solar_meter) / 1000, 2), 2, '.', ',');
            $c_plant->type = 'AE';

            $plants[] = $c_plant;
        }
        
        return new StatementCollection($plants, false);
    }
}
