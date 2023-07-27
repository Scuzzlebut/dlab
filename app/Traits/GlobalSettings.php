<?php

namespace App\Traits;

use DB;

trait GlobalSettings {
    private function getBaseClassName() {
        return strtolower(class_basename(get_class($this)));
    }

    public function get_value($field_name, $isMultiple = false) {
        $query = DB::table('settings')
            ->select('field_value')
            ->where('field_name', $field_name);

        if (!$isMultiple) {
            $val = $query->first();

            if (!empty($val)) {
                $val = current($val);
            }
        } else {
            $val = $query->pluck('field_value');
        }

        return $val;
    }

    public function set_value($field_name, $field_value, $internalCall = false) {
        if ($field_name == 'AUTH' && !$internalCall) {
            throw new \Exception('Error, internal use only key name', 666);
        }

        if (!is_array($field_value)) {
            $field_value = [$field_value];
        }

        foreach ($field_value as $val) {
            DB::table('settings')
                ->insert(
                    [
                        'field_name' => $field_name,
                        'field_value' => $val
                    ]
                );
        }
    }

    public function del_value($field_name, $internalCall = false, $field_value = NULL) {
        if ($field_name == 'AUTH' && !$internalCall) {
            throw new \Exception('Error, internal use only key name', 666);
        }

        $query = DB::connection('primary')
            ->table('settings')
            ->where('field_name', $field_name);

        if (!is_null($field_value)) {
            $query->where('field_value', $field_value);
        }

        return $query->delete();
    }

    public function update_value($field_name, $field_new_value) {
        if ($field_name == 'AUTH') {
            throw new \Exception('Error, internal use only key name', 666);
        }

        if ($field_new_value === NULL) {
            return $this->del_value($field_name);
        }

        DB::table('settings')
            ->where('field_name', $field_name)
            ->update(
                [
                    'field_value' => $field_new_value
                ]
            );
    }

    public function get_global_setting($field_name, $type = 'plain') {

        $res = $this->get_value($field_name, in_array($type, ['array', 'array-doubled', 'collection']));

        if ($res === NULL) {
            $res = config('global.' . $field_name);
        }

        if ($res === NULL && !in_array($type, ['forcedtext', 'array', 'array-doubled', 'collection'])) {
            return $res;
        }

        switch ($type) {
            case 'boolean':
            case 'bool':
                $res = filter_var($res, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'int':
            case 'integer':
                $res = intval($res);
                break;
            case 'float':
            case 'decimal':
            case 'double':
                $res = floatval($res);
                break;
            case 'plain':
            case 'forcedtext':
                $res = (string) $res;
                break;
            case 'array':
                if ($res === NULL) {
                    $res = [];
                } else if ($res instanceof \Illuminate\Support\Collection) {
                    $res = $res->toArray();
                }

                if (!is_array($res)) {
                    $res = [$res];
                }

                break;
            case 'array-doubled':
                if ($res === NULL) {
                    $res = [];
                } else if ($res instanceof \Illuminate\Support\Collection) {
                    $res = $res->toArray();
                }

                if (!is_array($res)) {
                    $res = [$res];
                }

                $res = array_combine(array_values($res), array_values($res));
                break;
            case 'collection':
                if ($res === NULL) {
                    $res = [];
                } else if (!$res instanceof \Illuminate\Support\Collection) {
                    $res = collect($res);
                }
            case 'nullable':
                if (empty($res)) {
                    return NULL;
                }
                return $res;
                break;
            default:
                break;
        }

        return $res;
    }

    public function set_global_setting($field_name, $field_value) {
        $res = $this->get_value($field_name);
        if ($res === NULL) {
            $this->set_value($field_name, $field_value);
        } else if (is_array($field_value)) {
            $this->del_value($field_name);
            $this->set_value($field_name, $field_value);
        } else {
            $this->update_value($field_name, $field_value);
        }
    }

    public function del_global_setting($field_name, $field_value = NULL) {
        $res = $this->del_value($field_name, false, $field_value);

        return $res;
    }
}
