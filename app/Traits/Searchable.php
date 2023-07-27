<?php

namespace App\Traits;

use Illuminate\Support\Carbon;

trait Searchable {
    public function scopeSearch($query, $terms, $separateWords = true) {
        if ($separateWords) {
            $terms = explode(' ', $terms);
        }

        if (!is_array($terms)) {
            $terms = [$terms];
        }

        $columnList = $this->searchable ?? $this->fillable;

        foreach ($terms as $term) {
            try {
                $date = Carbon::createFromFormat('d/m/Y', $term);
                if ($date->format('d/m/Y') === $term) {
                    $term = $date->format('Y-m-d');
                }
            } catch (\Throwable $th) {
            }
            $query->where(function ($q) use ($columnList, $term) {

                foreach ($columnList as $column) {
                    // se la colonna contiene : allora significa che è una relazione
                    if (stripos($column, ':') !== false) {
                        list($relation, $rel_columns) = explode(':', $column);
                        $rel_columns = array_map('trim', explode(',', $rel_columns)); // le colonne della relazione sono separate da una virgola
                        $q->orWhereHas($relation, function ($q2) use ($rel_columns, $term) {
                            $q2->where(function ($q3) use ($rel_columns, $term) {
                                foreach ($rel_columns as $rcol) {
                                    $q3->orWhere($rcol, 'LIKE', '%' . $term . '%');
                                }
                            });
                        });
                    } else {
                        $q->orWhere($column, 'LIKE', '%' . $term . '%');
                    }
                }
            });
        }
    }
}
