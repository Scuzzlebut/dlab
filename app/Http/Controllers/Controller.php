<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

//use Illuminate\Http\Request;
use App\Http\Requests\SimpleRequest as Request;

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function doTheSearch($items, Request $request, $separateWords = true) {
        if (!empty($request->search)) {
            $items->search($request->search, $separateWords);
        }

        return $items;
    }

    protected function doTheSort($items, Request $request, $allowedKeys = NULL) {
        if (!empty($request->sortBy)) {
            foreach ($request->sortBy as $key => $sortColumn) {
                if (is_null($allowedKeys) || in_array($sortColumn, $allowedKeys)) {
                    $items->orderBy($sortColumn, filter_var($request->sortDesc[$key], FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
                }
            }
        }

        return $items;
    }

    protected function doThePagination($items, Request $request) {
        return $items->paginate($request->getPerPage())->appends('itemsPerPage', $request->getPerPage());
    }
}
