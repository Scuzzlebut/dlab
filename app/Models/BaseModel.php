<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use DateTimeInterface;

class BaseModel extends Model {
    use HasFactory;
    use SoftDeletes;
    use Searchable;

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
        //return $date->toIso8601String();
    }
}
