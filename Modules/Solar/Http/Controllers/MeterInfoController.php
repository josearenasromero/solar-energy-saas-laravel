<?php

namespace Modules\Solar\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Exceptions\BadRequestException;
use Illuminate\Routing\Controller;
use Modules\RateAcuity\Entities\ScheduleDemandTime;
use Modules\Solar\Entities\MeterPlant;
use Modules\UtilityAPI\Entities\Meter;
use Modules\QOS\Entities\Plant;
use Modules\UtilityAPI\Http\Requests\Meter\IndexRequest;
use Modules\UtilityAPI\Resources\MeterInfoCollection;
use Modules\UtilityAPI\Resources\MeterInfoResource;
use Illuminate\Support\Facades\DB;
use Modules\RateAcuity\Entities\ScheduleEnergyTime;
use Modules\RateAcuity\Entities\ScheduleServiceCharge;

class MeterInfoController extends Controller
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
        $limit = $request->input('limit', 10000);
        $is_ae = $request->input('is_ae', true);
        
        $filter = $request->only(['meter_id', 'start_date', 'end_date', 'schedule_id', 'range', 'plant_id']);

        if (!isset($filter['range'])) {
            $filter['range'] = false;
        }

        $start_date = new \DateTime($filter['start_date']);
        $end_date = new \DateTime($filter['end_date']);
        $schedule_id = isset($filter['schedule_id']) ? $filter['schedule_id'] : null;
        $plant_id = isset($filter['plant_id']) ? $filter['plant_id'] : null;
        $meter_id = isset($filter['meter_id']) ? $filter['meter_id'] : null;
        $service_charge_rate = ScheduleServiceCharge::where('schedule_id', $schedule_id)->first();
        $service_charge_rate = isset($service_charge_rate->rate) ? (float) $service_charge_rate->rate : 0.0;

        //format by year-month YYYY-MM
        if ($end_date < $start_date) {
            throw new BadRequestException('End date cannot be lower than start date');
        }

        //get all entries for the specific schedule
        $all_energy_time = ScheduleEnergyTime::where('schedule_id', '=', $schedule_id)->get();
        $all_demand_time = ScheduleDemandTime::where('schedule_id', '=', $schedule_id)->get();

        $energy_time_dates = [];

        $diff = $end_date->diff($start_date);
        $years = [];
        for ($i = 0; $i <= $diff->y; $i++) {
            $years[] = (int) $start_date->format('Y') + $i;
        }

        foreach ($years as $year) {
            foreach ($all_energy_time as $current_energy_time) {
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

                $energy_time_dates[] = $energy_time_date;
            }

        }
        $energy_demand_dates = [];
        foreach ($years as $year) {
            foreach ($all_demand_time as $current_demand_time) {
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

                $energy_demand_dates[] = $demand_time_date;
            }
        }

        $ranges = [];
        $current_date = clone $start_date;
        while ($current_date <= $end_date) {
            $year_month = $current_date->format('Y-m');
            $ranges[$year_month] = [];
            $current_date->modify('first day of next month');
        }

        //now get to know each group by year-month        
        $i = 1;
        $values = [];

        $meter_grid_energy = [];
        foreach ($ranges as $year_month => $value) {
            $max_nc = [];
            $max_on = [];
            $meter = (object) [
                'id' => $i++,
                'bill_start_date' => '-',
                'bill_end_date' => '-',
                'bill_season' => '-',

                'net_on_peak' => '-',
                'net_part_peak' => '-',
                'net_off_peak' => '-',

                'max_nc' => '-',
                'max_on' => '-',

                'charge_other' => '-',
                'charge_nbc' => '-',
                'charge_energy' => '-',
                'charge_demand' => '-',
                'charge_total' => '-',
            ];

            $current_date = \DateTime::createFromFormat('Y-m', $year_month);
            $days_in_month = cal_days_in_month(CAL_GREGORIAN, (int) $current_date->format('m'), (int) $current_date->format('Y'));
            
            $seasons = [];
            $qos_on_peak = 0;
            $qos_part_peak = 0;
            $qos_off_peak = 0;

            $qos_peak_with_rate = [
                'on_peak' => 0,
                'part_peak' => 0,
                'off_peak' => 0,
            ];
            $demand_rate_value = 0.0;

            for ($j = 1; $j <= $days_in_month; $j++) {
                $padded_day = str_pad($j, 2, '0', STR_PAD_LEFT);
                $current_complete_date = new \DateTime($current_date->format('Y-m') . '-' . $padded_day);
                $energy_time = $this->find_energy_time_by_date($current_complete_date, $energy_time_dates);
                $energy_demand = $this->find_energy_time_by_date($current_complete_date, $energy_demand_dates);

                if (null === $energy_time)
                    continue;

                if (!isset($seasons[$energy_time->season])) {
                    $seasons[$energy_time->season] = 0;
                }
                $seasons[$energy_time->season] = $seasons[$energy_time->season] + 1;

                //\DB::enableQueryLog(); // Enable query log

                $current_complete_date_string = $current_complete_date->format('Y-m-d');

                $ae_measurements = null;
                $qos_measurements = null;

                if($is_ae) {
                    $ae_measurements = DB::table('ae_measurement')
                        ->join('ae_hardware', 'ae_hardware.id', '=', 'ae_measurement.ae_hardware_id')
                        ->join('ae_site', 'ae_site.id', '=', 'ae_hardware.ae_site_id')
                        ->leftJoin('qos_plant', 'qos_plant.ae_site_id', '=', 'ae_site.id')
                        ->where('qos_plant.id', '=', $plant_id)
                        ->where('ae_measurement.value', '>', 0)
                        ->whereBetween('ae_measurement.collected_at', [$current_complete_date_string . ' 00:00:00', $current_complete_date_string . ' 23:59:59'])
                        ->orderBy('ae_measurement.collected_at', 'ASC')
                        ->get([
                            'ae_hardware.id',
                            'ae_measurement.collected_at',
                            'ae_measurement.value',
                        ]);
                }

                if(!$is_ae) {
                    $qos_measurements = DB::table('qos_measurement')
                        ->join('qos_sensor', 'qos_sensor.id', '=', 'qos_measurement.sensor_id')
                        ->join('qos_inverter', 'qos_inverter.id', '=', 'qos_sensor.inverter_id')
                        ->join('qos_plant', 'qos_plant.id', '=', 'qos_inverter.plant_id')
                        ->where('qos_plant.id', '=', $plant_id)
                        ->where('qos_measurement.value', '>', 0)
                        ->whereBetween('qos_measurement.collected_at', [$current_complete_date_string . ' 00:00:00', $current_complete_date_string . ' 23:59:59'])
                        ->orderBy('qos_measurement.collected_at', 'ASC')
                        ->get([
                            'qos_inverter.id',
                            'qos_measurement.collected_at',
                            'qos_measurement.value',
                        ]);
                }

                //dd(\DB::getQueryLog()); // Show results of log

                if (null !== $qos_measurements && !$qos_measurements->isEmpty()) {
                    $inverters = [];
                    $inverters_date = [];
                    $filtered_inverters = array_unique($qos_measurements->toArray(), SORT_REGULAR);

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
                            $max_nc[] = round((($inverters['sum'][$k] - $inverters['diff'][$k])), 4);
                        }
                    }

                    //$qos_total = round(((array_sum($inverters['sum']) - array_sum($inverters['diff']))/1000), 4);
                    $qos_total = round(((array_sum($inverters['sum']) - array_sum($inverters['diff']))), 4);

                    $time_of_day = trim($energy_time->time_of_day);

                    $energy_rate = empty($energy_time->rate_kwh) ? 0.0 : (float) $energy_time->rate_kwh;
                    $demand_rate = empty($energy_demand->rate_kwh) ? 0.0 : (float) $energy_demand->rate_kwh;

                    if ($time_of_day == 'On-Peak' || $time_of_day == 'Peak') {
                        $qos_on_peak += $qos_total;
                        $qos_peak_with_rate['on_peak'] += (float) $energy_rate * $qos_total;
                        $demand_rate_value += $demand_rate * $qos_total; //note that will only consider time_of_day based on energy_time
                    }
                    if ($time_of_day == 'Off-Peak') {
                        $qos_off_peak += $qos_total;
                        $qos_peak_with_rate['off_peak'] += $energy_rate * $qos_total;
                        $demand_rate_value += $demand_rate * $qos_total;
                    }
                    if ($time_of_day == 'Part-Peak' || $time_of_day == 'Mid-Peak') {
                        $qos_part_peak += $qos_total;
                        $qos_peak_with_rate['part_peak'] += $energy_rate * $qos_total;
                        $demand_rate_value += $demand_rate * $qos_total;
                    }
                }

                //now the same but for AE
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
                            $max_nc[] = round((($inverters['sum'][$k] - $inverters['diff'][$k])), 4);
                        }
                    }

                    //$qos_total = round(((array_sum($inverters['sum']) - array_sum($inverters['diff']))/1000), 4);
                    $qos_total = round(((array_sum($inverters['sum']) - array_sum($inverters['diff']))), 4);

                    $time_of_day = trim($energy_time->time_of_day);

                    $energy_rate = empty($energy_time->rate_kwh) ? 0.0 : (float) $energy_time->rate_kwh;
                    $demand_rate = empty($energy_demand->rate_kwh) ? 0.0 : (float) $energy_demand->rate_kwh;

                    if ($time_of_day == 'On-Peak' || $time_of_day == 'Peak') {
                        $qos_on_peak += $qos_total;
                        $qos_peak_with_rate['on_peak'] += (float) $energy_rate * $qos_total;
                        $demand_rate_value += $demand_rate * $qos_total; //note that will only consider time_of_day based on energy_time
                    }
                    if ($time_of_day == 'Off-Peak') {
                        $qos_off_peak += $qos_total;
                        $qos_peak_with_rate['off_peak'] += $energy_rate * $qos_total;
                        $demand_rate_value += $demand_rate * $qos_total;
                    }
                    if ($time_of_day == 'Part-Peak' || $time_of_day == 'Mid-Peak') {
                        $qos_part_peak += $qos_total;
                        $qos_peak_with_rate['part_peak'] += $energy_rate * $qos_total;
                        $demand_rate_value += $demand_rate * $qos_total;
                    }
                }
            }

            //here we are back to month iteration
            $most_repeated_season = '-';
            if (count($seasons) > 0) {
                $most_repeated_season = array_search(max($seasons), $seasons);
            }

            $charge_other = $service_charge_rate * $days_in_month;

            $on_peak_rate = $qos_peak_with_rate['on_peak'];
            $off_peak_rate = $qos_peak_with_rate['off_peak'];
            $part_peak_rate = $qos_peak_with_rate['part_peak'];

            $charge_energy = round($on_peak_rate + $off_peak_rate + $part_peak_rate, 4);
            
            $charge_demand = $demand_rate_value;
            $charge_total = round($charge_energy + $charge_demand, 4);

            $meter->bill_start_date = $current_date->format('Y-m');
            $meter->bill_end_date = $current_date->format('Y-m');
            $meter->bill_season = trim($most_repeated_season);

            $meter->net_on_peak = ($qos_on_peak === 0) ? '-' : number_format(round($qos_on_peak / 1000, 2), 2, '.', ',');
            $meter->net_part_peak = ($qos_part_peak === 0) ? '-' : number_format(round($qos_part_peak / 1000, 2), 2, '.', ',');
            $meter->net_off_peak = ($qos_off_peak === 0) ? '-' : number_format(round($qos_off_peak / 1000, 2), 2, '.', ',');
            
            $meter->max_nc = (!empty($max_nc)) ? number_format(round(max($max_nc) / 1000, 2), 2, '.', ',') : '-';
            $meter->max_on = (!empty($max_on)) ? number_format(round(max($max_on) / 1000, 2), 2, '.', ',') : '-';

            $meter->charge_other = ($charge_other === 0) ? '-' : number_format(round($charge_other, 2), 2, '.', ',');
            $meter->charge_nbc = '-';
            $meter->charge_energy = ($charge_energy === 0) ? '-' : number_format(round($charge_energy / 1000, 2), 2, '.', ',');
            $meter->charge_demand = ($charge_demand === 0) ? '-' : number_format(round($charge_demand / 1000, 2), 2, '.', ',');
            $meter->charge_total = ($charge_total === 0) ? '-' : number_format(round($charge_total / 1000, 2), 2, '.', ',');

            $values[] = new MeterInfoResource($meter);
        }

        return new MeterInfoCollection($values);
    }

    public function indexUtility(IndexRequest $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10000);

        $filter = $request->only(['meter_id', 'start_date', 'end_date', 'schedule_id', 'range', 'plant_id']);

        if (!isset($filter['range'])) {
            $filter['range'] = false;
        }

        // if(!isset($filter['meter_id']))
        // {
        // return new MeterInfoCollection([]);
        // }

        /*$meters = Meter::where('id',$filter['meter_id'])->with('meter_plant', 'meter_plant.schedule')->get();

        if(!$meters)
        {
            throw new NotFoundException('Meter not found');
        }*/

        $start_date = new \DateTime($filter['start_date']);
        $original_start_date = new \DateTime($filter['start_date']);
        $end_date = new \DateTime($filter['end_date']);
        $original_end_date = new \DateTime($filter['end_date']);
        $schedule_id = isset($filter['schedule_id']) ? $filter['schedule_id'] : null;
        $plant_id = isset($filter['plant_id']) ? $filter['plant_id'] : null;
        $meter_id = isset($filter['meter_id']) ? $filter['meter_id'] : null;
        $service_charge_rate = ScheduleServiceCharge::where('schedule_id', $schedule_id)->first();
        $service_charge_rate = isset($service_charge_rate->rate) ? (float) $service_charge_rate->rate : 0.0;

        //format by year-month YYYY-MM
        if ($end_date < $start_date) {
            throw new BadRequestException('End date cannot be lower than start date');
        }

        if (null === $meter_id) {
            throw new BadRequestException('Meter ID is needed');
        }

        if (null === $plant_id) {
            throw new BadRequestException('Plant ID is needed');
        }

        $plant = Plant::find($plant_id);

        if (null === $plant) {
            throw new BadRequestException('Invalid plant');
        }

        //get all entries for the specific schedule
        $all_energy_time = ScheduleEnergyTime::where('schedule_id', '=', $schedule_id)->get();
        $all_demand_time = ScheduleDemandTime::where('schedule_id', '=', $schedule_id)->get();

        $energy_time_dates = [];

        $diff = $end_date->diff($start_date);
        $years = [];
        for ($i = 0; $i <= $diff->y; $i++) {
            $years[] = (int) $start_date->format('Y') + $i;
        }

        foreach ($years as $year) {
            foreach ($all_energy_time as $current_energy_time) {
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

                $energy_time_dates[] = $energy_time_date;
            }

        }
        $energy_demand_dates = [];
        foreach ($years as $year) {
            foreach ($all_demand_time as $current_demand_time) {
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

                $energy_demand_dates[] = $demand_time_date;
            }
        }

        $ranges = [];
        $current_date = clone $start_date;
        while ($current_date <= $end_date) {
            $year_month = $current_date->format('Y-m');
            $ranges[$year_month] = [];
            $current_date->modify('first day of next month');
        }

        //now get to know each group by year-month        
        $i = 1;
        $values = [];

        $meter_grid_energy = [];
        foreach ($ranges as $year_month => $value) {
            $max_nc = [];
            $max_on = [];
            $meter = (object) [
                'id' => $i++,
                'bill_start_date' => '-',
                'bill_end_date' => '-',
                'bill_season' => '-',

                'net_on_peak' => '-',
                'net_part_peak' => '-',
                'net_off_peak' => '-',

                'max_nc' => '-',
                'max_on' => '-',

                'charge_other' => '-',
                'charge_nbc' => '-',
                'charge_energy' => '-',
                'charge_demand' => '-',
                'charge_total' => '-',
            ];

            $meter_utility = (object) [
                'id' => $i,
                'bill_start_date' => '-',
                'bill_end_date' => '-',
                'bill_season' => '-',

                'net_on_peak' => '-',
                'net_part_peak' => '-',
                'net_off_peak' => '-',

                'max_nc' => '-',
                'max_on' => '-',

                'charge_other' => '-',
                'charge_nbc' => '-',
                'charge_energy' => '-',
                'charge_demand' => '-',
                'charge_total' => '-',
            ];

            $current_date = \DateTime::createFromFormat('Y-m', $year_month);
            $init = 1;

            $days_in_month = cal_days_in_month(CAL_GREGORIAN, (int) $current_date->format('m'), (int) $current_date->format('Y'));

            if (1 === (int) $filter['range'] && $current_date->format('Y-m') === $original_start_date->format('Y-m'))
            {
                    $init = $original_start_date->format('d');
            }
            
            if (1 === (int) $filter['range'] && $current_date->format('Y-m') === $original_end_date->format('Y-m') && $days_in_month > $original_end_date->format('d')) {   
                $days_in_month = $original_end_date->format('d');
            }

            $seasons = [];
            $qos_on_peak = 0;
            $qos_part_peak = 0;
            $qos_off_peak = 0;

            $qos_peak_with_rate = [
                'on_peak' => 0,
                'part_peak' => 0,
                'off_peak' => 0,
            ];
            $demand_rate_value = 0.0;

            $utility_peak = [
                'on_peak' => 0,
                'part_peak' => 0,
                'off_peak' => 0,
            ];

            $utility_peak_with_rate = [
                'on_peak' => 0,
                'part_peak' => 0,
                'off_peak' => 0,
            ];

            $utility_demand_rate_value = 0.0;

            $utilityapi_value = [
                'kwh' => 0.0,
                'net' => 0.0,
                'fwd' => 0.0,
            ];

            for ($j = $init; $j <= $days_in_month; $j++) {
                $padded_day = str_pad($j, 2, '0', STR_PAD_LEFT);
                $current_complete_date = new \DateTime($current_date->format('Y-m') . '-' . $padded_day);
                $energy_time = $this->find_energy_time_by_date($current_complete_date, $energy_time_dates);
                $energy_demand = $this->find_energy_time_by_date($current_complete_date, $energy_demand_dates);

                if (null === $energy_time)
                    continue;

                if (!isset($seasons[$energy_time->season])) {
                    $seasons[$energy_time->season] = 0;
                }
                $seasons[$energy_time->season] = $seasons[$energy_time->season] + 1;

                //\DB::enableQueryLog(); // Enable query log

                $current_complete_date_string = $current_complete_date->format('Y-m-d');
                $qos_measurements = DB::table('qos_measurement')
                    ->join('qos_sensor', 'qos_sensor.id', '=', 'qos_measurement.sensor_id')
                    ->join('qos_inverter', 'qos_inverter.id', '=', 'qos_sensor.inverter_id')
                    ->join('qos_plant', 'qos_plant.id', '=', 'qos_inverter.plant_id')
                    ->where('qos_plant.id', '=', 4)
                    ->where('qos_measurement.value', '>', 0)
                    ->whereBetween('qos_measurement.collected_at', [$current_complete_date_string . ' 00:00:00', $current_complete_date_string . ' 23:59:59'])
                    ->orderBy('qos_measurement.collected_at', 'ASC')
                    ->get([
                        'qos_inverter.id',
                        'qos_measurement.collected_at',
                        'qos_measurement.value',
                    ]);

                $meters_id = $plant->meter->pluck('id');
                $utilityapi_measurements = DB::table('utilityapi_measurements')
                    ->whereBetween('end_at', [$current_complete_date_string . ' 00:00:00', $current_complete_date_string . ' 23:59:59'])
                    //->where('meter_id', $meter_id)
                    ->whereIn('meter_id', $meters_id)
                    ->orderBy('utilityapi_measurements.end_at', 'ASC')
                    ->get([
                        'meter_id',
                        'end_at',
                        'kwh_value',
                        'datapoints'
                    ]);

                //dd(\DB::getQueryLog()); // Show results of log

                if (!$utilityapi_measurements->isEmpty()) {
                    $kwh = [];
                    //$meter_grid_energy = 0;
                    $meter_grid_percent = 0;
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

                    //$utilityapi_energy_fwd = isset($kwh['fwd']) ? array_sum($kwh['fwd']) : 0;
                    //$utilityapi_energy = isset($kwh['net']) ? array_sum($kwh['net']) : 0;

                    $target_meter_percentage = isset($meter_grid_energy[$meter_id]) ? round($meter_grid_energy[$meter_id] / array_sum($meter_grid_energy), 4) : 0.0;
                }

                if (!$qos_measurements->isEmpty()) {
                    $inverters = [];
                    $inverters_date = [];
                    $filtered_inverters = array_unique($qos_measurements->toArray(), SORT_REGULAR);

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
                            $max_nc[] = round((($inverters['sum'][$k] - $inverters['diff'][$k])), 4);
                        }
                    }

                    //$qos_total = round(((array_sum($inverters['sum']) - array_sum($inverters['diff']))/1000), 4);
                    $qos_total = round(((array_sum($inverters['sum']) - array_sum($inverters['diff']))), 4) * $target_meter_percentage;

                    $time_of_day = trim($energy_time->time_of_day);

                    $energy_rate = empty($energy_time->rate_kwh) ? 0.0 : (float) $energy_time->rate_kwh;
                    $demand_rate = empty($energy_demand->rate_kwh) ? 0.0 : (float) $energy_demand->rate_kwh;

                    if ($time_of_day == 'On-Peak' || $time_of_day == 'Peak') {
                        $qos_on_peak += $qos_total;
                        $qos_peak_with_rate['on_peak'] += (float) $energy_rate * $qos_total;
                        $demand_rate_value += $demand_rate * $qos_total; //note that will only consider time_of_day based on energy_time
                    }
                    if ($time_of_day == 'Off-Peak') {
                        $qos_off_peak += $qos_total;
                        $qos_peak_with_rate['off_peak'] += $energy_rate * $qos_total;
                        $demand_rate_value += $demand_rate * $qos_total;
                    }
                    if ($time_of_day == 'Part-Peak' || $time_of_day == 'Mid-Peak') {
                        $qos_part_peak += $qos_total;
                        $qos_peak_with_rate['part_peak'] += $energy_rate * $qos_total;
                        $demand_rate_value += $demand_rate * $qos_total;
                    }
                }
            }

            //here we are back to month iteration
            $most_repeated_season = '-';
            if (count($seasons) > 0) {
                $most_repeated_season = array_search(max($seasons), $seasons);
            }

            $charge_other = $service_charge_rate * $days_in_month;

            $on_peak_rate = $qos_peak_with_rate['on_peak'];
            $off_peak_rate = $qos_peak_with_rate['off_peak'];
            $part_peak_rate = $qos_peak_with_rate['part_peak'];

            //$utility_on_peak_rate = $utility_peak_with_rate['on_peak'];
            //$utility_off_peak_rate = $utility_peak_with_rate['off_peak'];
            //$utility_part_peak_rate = $utility_peak_with_rate['part_peak'];

            $charge_energy = round($on_peak_rate + $off_peak_rate + $part_peak_rate, 4);
            //$utility_charge_energy = round($utility_on_peak_rate + $utility_off_peak_rate + $utility_part_peak_rate, 4);

            $charge_demand = $demand_rate_value;
            $charge_total = round($charge_energy + $charge_demand, 4);

            //$utility_charge_demand = $utility_demand_rate_value;
            //$utility_charge_total = round($utility_charge_energy+$utility_charge_demand, 4);

            $meter->bill_start_date = $current_date->format('Y-m');
            $meter->bill_end_date = $current_date->format('Y-m');
            $meter->bill_season = trim($most_repeated_season);

            $meter->net_on_peak = ($qos_on_peak === 0) ? '-' : number_format($qos_on_peak, 2, '.', ',');
            $meter->net_part_peak = ($qos_part_peak === 0) ? '-' : number_format($qos_part_peak, 2, '.', ',');
            $meter->net_off_peak = ($qos_off_peak === 0) ? '-' : number_format($qos_off_peak, 2, '.', ',');
            ;

            $meter->max_nc = (!empty($max_nc)) ? max($max_nc) : '-';
            $meter->max_on = (!empty($max_on)) ? max($max_on) : '-';

            $meter->charge_other = ($charge_other === 0) ? '-' : number_format(round($charge_other, 4), 2, '.', ',');
            $meter->charge_nbc = '-';
            $meter->charge_energy = ($charge_energy === 0) ? '-' : number_format(round($charge_energy, 4), 2, '.', ',');
            $meter->charge_demand = ($charge_demand === 0) ? '-' : number_format(round($charge_demand, 4), 2, '.', ',');
            $meter->charge_total = ($charge_total === 0) ? '-' : number_format(round($charge_total, 4), 2, '.', ',');

            $values[] = new MeterInfoResource($meter);
        }

        if (1 === (int) $filter['range']) {
            $meter_range = (object) [
                'id' => 1,
                'bill_start_date' => '-',
                'bill_end_date' => '-',
                'bill_season' => '-',

                'net_on_peak' => '-',
                'net_part_peak' => '-',
                'net_off_peak' => '-',

                'max_nc' => '-',
                'max_on' => '-',

                'charge_other' => '-',
                'charge_nbc' => '-',
                'charge_energy' => '-',
                'charge_demand' => '-',
                'charge_total' => '-',
            ];
            $net_on_peak_range = 0.0;
            $net_part_peak_range = 0.0;
            $net_off_peak_range = 0.0;
            $charge_other_range = 0.0;
            $charge_nbc_range = 0.0;
            $charge_energy_range = 0.0;
            $charge_demand_range = 0.0;
            $charge_total_range = 0.0;

            $max_nc_range = [];
            $max_on_range = [];

            foreach ($values as $value) {
                $net_on_peak_range += ('-' === $value->net_on_peak) ?  0.0 : $value->net_on_peak;
                $net_part_peak_range += ('-' === $value->net_part_peak) ?  0.0 : $value->net_part_peak;
                $net_off_peak_range += ('-' === $value->net_off_peak) ?  0.0 : $value->net_off_peak;
                $charge_other_range += ('-' === $value->charge_other) ?  0.0 : $value->charge_other;
                $charge_energy_range += ('-' === $value->charge_energy) ?  0.0 : $value->charge_energy;
                $charge_demand_range += ('-' === $value->charge_demand) ?  0.0 : $value->charge_demand;
                $charge_total_range += ('-' === $value->charge_total) ?  0.0 : $value->charge_total;

                $max_nc_range[] = ('-' === $value->max_nc) ? 0.0 : $value->max_nc;
                $max_on_range[] = ('-' === $value->max_on) ? 0.0 : $value->max_on;
            }

            $meter_range->bill_start_date = $original_start_date->format('Y-m-d H:i:s');
            $meter_range->bill_end_date = $original_end_date->format('Y-m-d H:i:s');
            $meter_range->bill_season = '-';

            $meter_range->net_on_peak = ($net_on_peak_range === 0) ? '-' : number_format($net_on_peak_range, 2, '.', ',');
            $meter_range->net_part_peak = ($net_part_peak_range === 0) ? '-' : number_format($net_part_peak_range, 2, '.', ',');
            $meter_range->net_off_peak = ($net_off_peak_range === 0) ? '-' : number_format($net_off_peak_range, 2, '.', ',');

            $meter_range->max_nc = (!empty($max_nc_range)) ? max($max_nc_range) : '-';
            $meter_range->max_on = (!empty($max_on_range)) ? max($max_on_range) : '-';

            $meter_range->charge_other = ($charge_other_range === 0) ? '-' : number_format(round($charge_other_range, 4), 2, '.', ',');
            $meter_range->charge_nbc = '-';
            $meter_range->charge_energy = ($charge_energy_range === 0) ? '-' : number_format(round($charge_energy_range, 4), 2, '.', ',');
            $meter_range->charge_demand = ($charge_demand_range === 0) ? '-' : number_format(round($charge_demand_range, 4), 2, '.', ',');
            $meter_range->charge_total = ($charge_total_range === 0) ? '-' : number_format(round($charge_total_range, 4), 2, '.', ',');

            $values = [new MeterInfoResource($meter_range)];
        }

        return new MeterInfoCollection($values);
    }

}
