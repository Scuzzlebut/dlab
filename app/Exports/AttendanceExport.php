<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class AttendanceExport extends BasicModelExport implements WithColumnFormatting, WithStrictNullComparison, WithMapping {
    private $ids; // array

    public function __construct($filename, $ids) {
        $this->ids = $ids;
        parent::__construct($filename, Attendance::class);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection() {
        if ($this->class === NULL) {
            return collect([]);
        }

        /*$attendances = $this->class::select([DB::raw("*, SUM(hours) as total_hours")])*/

        $sort = 'asc';
        $attendances = $this->class::whereIn('id', $this->ids)
            /*->with(['staff' => function ($query) use ($sort) {
                $query->orderBy('surname', $sort)->orderBy('name', $sort);
            }])*/
            ->with(['staff', 'type', 'approver', 'applicant'])
            ->orderBy('date_start', 'asc')
            ->orderBy('type_id', 'asc')
            ->get();

        return $attendances;
    }

    public function headings(): array {
        return [
            'Dipendente',
            'Data inizio',
            'Data fine',
            'Certificato medico',
            'Note',
            'Ore',
            'Tipologia',
            'Accettata',
        ];
    }

    public function columnFormats(): array {
        return [
            'B' => 'dd/mm/yyyy hh:mm',
            'C' => 'dd/mm/yyyy hh:mm',
        ];
    }

    public function map($row): array {
        return [
            $row->staff->fullname,
            Date::dateTimeToExcel($row->date_start),
            Date::dateTimeToExcel($row->date_end),
            $row->sick_note,
            $row->note,
            $row->hours,
            $row->type->type_name,
            boolToHumanString($row->accepted)
        ];
    }
}
