<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('get_os_system')) {
    function get_os_system() {
        if (DIRECTORY_SEPARATOR === '\\') {
            return "WIN";
        }
        if (DIRECTORY_SEPARATOR === '/') {
            return "UNIX";
        }
        return null;
    }
}
if (!function_exists('dd_sql_bind')) {
    function dd_sql_bind($query) {
        return vsprintf(str_replace('?', '%s', str_replace('%', '%%', $query->toSql())), collect($query->getBindings())->map(function ($binding) {
            return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());
    }
}

if (!function_exists('validate_email')) {
    function validate_email($string) {
        return preg_match("/([a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,})/i", $string);
    }
}

if (!function_exists('temp_storage_path')) {
    function temp_storage_path($path) {
        $path = ltrim($path, '/');
        return Storage::disk('local_tmp')->path($path);
    }
}

if (!function_exists('get_client_real_ip')) {
    // Request::ip() restitusce l'ip del server se siamo dietro ad un load balancer o simile (tipo cloudflare!)
    // https://stackoverflow.com/questions/33268683/how-to-get-client-ip-address-in-laravel-5/41769505#41769505
    function get_client_real_ip() {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return request()->ip(); // it will return server ip when no client ip found
    }
}

if (!function_exists('boolToHumanString')) {
    function boolToHumanString($value) {
        return $value ? 'Sì' : 'No';
    }
}

if (!function_exists('is_date_helper')) {
    function is_date_helper($stringDate) {
        return ((1 === preg_match('"^[0-9]{2}[\/\-\.]{1}[0-9]{2}[\/\-\.]{1}[0-9]{2}$"', $stringDate)) || (1 === preg_match('"[0-9]{2}[\/\-\.]{1}[0-9]{2}[\/\-\.]{1}[0-9]{4}"', $stringDate)) || (1 === preg_match('"[0-9]{4}[\/\-\.]{1}[0-9]{2}[\/\-\.]{1}[0-9]{2}"', $stringDate)));
    }
}

if (!function_exists('format_date_for_db')) {
    function format_date_for_db($stringDate, $withTail = true, $checkDate = false, $checkAlsoTime = false) {
        if (!is_date_helper($stringDate))
            return FALSE;

        //if is alreay formatted nothing to do
        if (1 === preg_match('"[0-9]{4}[\/\-\.]{1}[0-9]{2}[\/\-\.]{1}[0-9]{2}"', $stringDate)) {
            $date = $stringDate;
        } else {
            $date_array = [];

            if ((1 === preg_match('"^([0-9]{2})[\/\-\.]{1}([0-9]{2})[\/\-\.]{1}([0-9]{2})$"', $stringDate, $date_array))) {
                $date = '20' . $date_array[3] . '-' . $date_array[2] . '-' . $date_array[1];
                if ($withTail) {
                    $date .= $date_array[4];
                }
            } else if ((1 === preg_match('"([0-9]{2})[\/\-\.]{1}([0-9]{2})[\/\-\.]{1}([0-9]{4})(.*)"', $stringDate, $date_array))) {
                $date = $date_array[3] . '-' . $date_array[2] . '-' . $date_array[1];
                if ($withTail) {
                    $date .= $date_array[4];
                }
            } else {
                return FALSE;
            }
        }

        if ($checkDate && !$withTail) {

            $format = 'Y-m-d';

            if ($checkAlsoTime) {
                $format = strlen($date) == 19 ? 'Y-m-d H:m:s' : 'Y-m-d H:m';
            }

            $res = DateTime::createFromFormat($format, $date);
            if ($res === FALSE) {
                return FALSE;
            }
        }

        return $date;
    }
}

if (!function_exists('password_validator_rules')) {
    function password_validator_rules($required = true) {
        $isRequired = 'required|';
        if (!$required)
            $isRequired = '';
        //return $isRequired.'string|min:12|confirmed|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[@$!%*#?&]).*$/';
        return $isRequired . 'string|min:10|confirmed|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/';
    }
}

if (!function_exists('password_validator_rules_custom_error')) {
    function password_validator_rules_custom_error() {
        return 'La password deve contenere almeno una lettera maiuscola, minuscola e un numero.';
    }
}

if (!function_exists('friendly_string')) {
    function friendly_string($str, $ch_replaced = '-') {
        $str = str_replace(' ', $ch_replaced, $str);
        $str = preg_replace('/[^A-Za-z0-9\"' . $ch_replaced . '"]/', $ch_replaced, $str);
        return preg_replace('/-+/', $ch_replaced, $str);
    }
}
