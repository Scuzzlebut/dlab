<?php

namespace App\Models\Scopes;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class ActiveStaff implements Scope {
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model) {
        $builder->whereNull($model->getTable() . '.date_end')
            ->orWhereDate($model->getTable() . '.date_end', '>', format_date_for_db(Carbon::now()));
    }
}
