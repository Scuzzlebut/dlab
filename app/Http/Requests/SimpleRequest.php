<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as BaseRequest;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

class SimpleRequest extends BaseRequest {
    public function rules() {
        return [];
    }

    public function authorize() {
        return true;
    }

    public function getPerPage() {
        if (isset($this->itemsPerPage)) {

            if ($this->itemsPerPage == -1) {
                return 1000000;
            }

            return $this->itemsPerPage;
        } else {
            return 15; //Auth::user()->settings('DefaultRowsInTable');
        }
    }
}
