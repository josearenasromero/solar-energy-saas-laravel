<?php

namespace Modules\Common\Entities;

use Illuminate\Database\Eloquent\Model;
use Exception;

class ExtractionLog extends Model
{

    protected $fillable = [
        'id',
        'entity_name',
        'attempt',
        'status',
        'message',
        'start_extracted_date',
        'end_extracted_date',
        'attempt_date',
        'table_name',
        'key_name',
        'key_value'
    ];

    protected $table = 'extraction_log';

    public function attempt($entity_name, $table_name, $key_name, $key)
    {
        $log = ExtractionLog::where([
            ['entity_name', $entity_name],
            ['table_name', $table_name],
            ['key_name', $key_name],
            ['key_value', $key]
        ])->orderBy('attempt', 'desc')->first();

        $attempts = isset($log->attempt) ? $log->attempt + 1 : 1;
        $attempt_date = new \DateTime('now', new \DateTimeZone('UTC'));

        return ['attempt' => $attempts, 'date' => $attempt_date->format('Y-m-d H:i:s')];
    }

    public function errorOnRequest($entity_name, $table_name, $key_name, $key, $attempt, $status = 'Error', $message = 'Error on request')
    {
        ExtractionLog::create([
            'entity_name' => $entity_name,
            'attempt' => $attempt['attempt'],
            'status' => $status,
            'message' => $message,
            'attempt_date' => $attempt['date'],
            'table_name' => $table_name,
            'key_name' => $key_name,
            'key_value' => $key
        ]);
    }

    public function createOrError(Model $model, $entity_name, $table_name, $key_name, $key, $object, $attempt)
    {
        $log = ExtractionLog::where([
            ['entity_name', $entity_name],
            ['table_name', $table_name],
            ['key_name', $key_name],
            ['key_value', $key]
        ])->orderBy('attempt', 'desc')->first();

        $qty_attempts = isset($log->attempt) ? $log->attempt + 1 : 1;

        if (!(isset($log_utility->status) && 'completed' === strtolower($log->status))) {
            try {

                $model::create($object);

                ExtractionLog::create([
                    'entity_name' => $entity_name,
                    'attempt' => $qty_attempts,
                    'status' => 'Completed',
                    'message' => 'Success',
                    'attempt_date' => $attempt['date'],
                    'table_name' => $table_name,
                    'key_name' => $key_name,
                    'key_value' => $key
                ]);

            } catch (Exception $e) {

                ExtractionLog::create([
                    'entity_name' => $entity_name,
                    'attempt' => $qty_attempts,
                    'status' => 'Error',
                    'message' => 'Error on insert',
                    'attempt_date' => $attempt['date'],
                    'table_name' => $table_name,
                    'key_name' => $key_name,
                    'key_value' => $key
                ]);

            }
        }
    }

}
