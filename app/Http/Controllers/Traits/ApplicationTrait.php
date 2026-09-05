<?php

namespace App\Http\Controllers\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ApplicationTrait
{
    // last tested on 21 Aug 2026 01:48 AM
    protected function ddError($data_value): void
    {
        dd($data_value);
    }
    // last tested on 21 Aug 2026 01:48 AM
    protected function enableQueryLog($ddEnd = false): void
    {
        DB::listen(function ($query) use($ddEnd) {
            $sql = $query->sql;

            foreach ($query->bindings as $binding) {
                if (is_string($binding)) {
                    $value = "'{$binding}'";
                } else {
                    $value = (is_null($binding) ? 'NULL' : $binding);
                }
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }

            dump([
                'sql' => $sql,
                'time' => $query->time . ' ms',
            ]);
            $ddEnd ? dd('query end') : '';
        });
    }
    // last tested on 21 Aug 2026 01:48 AM

    protected function appLog(
        array $logMsg = [],
        string $type = 'info',
        bool $echoValue = false
    ): void {
        $logId = Str::random(8);

        Log::{$type}('Log Start', [
            'log_id' => $logId,
        ]);

        if ($echoValue) {
            echo "<br>Log Start [{$logId}]<br>";
        }

        foreach ($logMsg as $key => $msg) {

            // Convert value to something loggable
            if (is_array($msg) || is_object($msg)) {
                $logValue = json_encode(
                    $msg,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            } elseif (is_bool($msg)) {
                $logValue = $msg ? 'true' : 'false';
            } elseif (is_null($msg)) {
                $logValue = 'NULL';
            } else {
                $logValue = (string) $msg;
            }

            Log::{$type}('Application Log', [
                'log_id' => $logId,
                'key' => $key,
                'message' => $logValue,
            ]);

            if ($echoValue) {
                echo "key: {$key} | message: {$logValue}<br>";
            }
        }

        Log::{$type}('Log End', [
            'log_id' => $logId,
        ]);

        if ($echoValue) {
            echo "Log End [{$logId}]<br>";
        }
    }
}
