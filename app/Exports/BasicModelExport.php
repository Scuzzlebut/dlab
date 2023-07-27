<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BasicModelExport implements FromCollection, WithHeadings, ShouldAutoSize {

    use Exportable;

    private $fileName = 'export.xls';
    protected $class = NULL;

    public function __construct($filename, $class) {
        $this->fileName = $filename;
        $this->class = $class;
    }

    /*protected function doTheSearch($items, $search, $separateWords = true)
    {
        if (!empty($search)) {
            $items->search($search, $separateWords);
        }
        return $items;
    }

    protected function doTheSort($items, $sortBy, $sortDesc)
    {
        if (!empty($sortBy)) {
            foreach ($sortBy as $key => $sortColumn) {
                $items->orderBy($sortColumn, filter_var($sortDesc[$key], FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            }
        }
        return $items;
    }*/

    public function collection() {

        if ($this->class === NULL) {
            return collect([]);
        }

        return $this->class::all($this->class::$exportable)->each->setAppends([]);
    }

    public function headings(): array {

        if ($this->class === NULL) {
            return [];
        }

        return $this->class::$exportableLabels ?? $this->class::$exportable;
    }
}
