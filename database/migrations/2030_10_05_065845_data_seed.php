<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $files = [];

        $Iterator = new DirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . 'seed');
        foreach (new IteratorIterator($Iterator) as $filename => $object) {
            if ('sql' === strtolower($object->getExtension())) {
                $file = __DIR__ . DIRECTORY_SEPARATOR . 'seed' . DIRECTORY_SEPARATOR . $object->getFileName();
                $files[] = $file;
            }
        }

        $db = new PDO('mysql:dbname=' . env('DB_DATABASE') . ';host=' . env('DB_HOST') . ';port=' . env('DB_PORT'), env('DB_USERNAME'), env('DB_PASSWORD'));
        $db->query('SET foreign_key_checks = 0');
        //$db->query('SET GLOBAL max_allowed_packet = 134217728');
        foreach ($files as $file) {
            $sql = file_get_contents($file);
            $db->exec($sql);
        }
        $db->query('SET foreign_key_checks = 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
