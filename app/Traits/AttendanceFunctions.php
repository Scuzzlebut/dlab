<?php

namespace App\Traits;

use App\Models\AttendanceType;
use App\Models\User;
use DateTime;
use App\Models\Staff;
use App\Models\Attendance;
use App\Traits\GlobalSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

trait AttendanceFunctions {
    use GlobalSettings;

    public function getHolidaysDates($year = NULL) {

        if (empty($year)) {
            $year = date('Y');
        }

        $holidays = json_decode($this->get_global_setting('days_holidays'), true);

        foreach ($holidays as $holiday_name => &$holiday_DM) {
            $holiday_DM = Carbon::parse($year . '-' . $holiday_DM);
        }

        //calcolo Pasqua e Pasquetta
        $easter = Carbon::parse(easter_date($year))->timezone('Europe/Rome');
        $easter_monday = $easter->copy()->addDays(1);

        $holidays['Pasqua'] = $easter;
        $holidays['Pasquetta'] = $easter_monday;

        return $holidays;
    }

    public function calcAttendanceMinutes($date_start, $date_end, $staff_id = null) {
        $attendance_time = 0;
        $working_staff_hours = [];
        $times_fields_check = ['morning_starttime', 'morning_endtime', 'afternoon_starttime', 'afternoon_endtime'];

        //se l'id è nullo allora recupero gli orari standard aziendali altrimenti quelli del dipendente (che potrebbero variare)
        if (is_null($staff_id)) {
            foreach ($times_fields_check as $index => $key) {
                $working_staff_hours[$key] = Carbon::parse($this->get_global_setting($key)); //->format('H:i');
            }
        } else {
            $staff_employee = Staff::findOrFail($staff_id);
            foreach ($times_fields_check as $index => $key) {
                $working_staff_hours[$key] = Carbon::parse($staff_employee[$key]); //->format('H:i');
            }
        }

        $date_start = DateTime::createFromFormat('Y-m-d H:i:s', $date_start); //attendance start
        $date_end = DateTime::createFromFormat('Y-m-d H:i:s', $date_end); //attendance end
        $holidays = $this->getHolidaysDates();

        if ($date_end > $date_start) {
            $first_day = $date_start->format('Y-m-d');
            $first_day_dt = DateTime::createFromFormat('Y-m-d H:i:s', $first_day . " 00:00:00");
            $last_day = $date_end->format('Y-m-d');
            $last_day_dt = DateTime::createFromFormat('Y-m-d H:i:s', $last_day . " 23:59:59");

            for ($attendance_day = $first_day_dt; $attendance_day <= $last_day_dt; $attendance_day->modify('+1 day')) {
                $isHoliday = false;

                //escludiamo sabato e domenica
                if ($attendance_day->format('l') === 'Saturday' || $attendance_day->format('l') === 'Sunday')
                    $isHoliday = true;

                //ed escludiamo le vacanze
                foreach ($holidays as $k => $holiday) {
                    //posso farlo perchè Carbon è un'istanza di DateTime
                    if ($attendance_day == $holiday) {
                        $isHoliday = true;
                        break;
                    }
                }

                //se è un giorno lavorativo allora procedo al conteggio dei minuti quotidiani
                if (!$isHoliday) {
                    $d_start = Carbon::parse($date_start);
                    $d_end = Carbon::parse($date_end);

                    $attendance_day_f = $attendance_day->format('Y-m-d');
                    $mins_working_time = $staff_employee->getWorkedTimePerDay('minutes'); //minuti lavorati al giorno
                    $mins_lunch_time = $staff_employee->getLunchTimePerDay('minutes'); //minuti fascia pranzo

                    $morning_start = Carbon::parse($attendance_day_f)->copy()->setTime(explode(':', $working_staff_hours['morning_starttime']->toTimeString())[0], explode(':', $working_staff_hours['morning_starttime']->toTimeString())[1]);
                    $lunch_time_start = Carbon::parse($attendance_day_f)->copy()->setTime(explode(':', $working_staff_hours['morning_endtime']->toTimeString())[0], explode(':', $working_staff_hours['morning_endtime']->toTimeString())[1]);
                    $lunch_time_end = Carbon::parse($attendance_day_f)->copy()->setTime(explode(':', $working_staff_hours['afternoon_starttime']->toTimeString())[0], explode(':', $working_staff_hours['afternoon_starttime']->toTimeString())[1]);
                    $afternoon_end = Carbon::parse($attendance_day_f)->copy()->setTime(explode(':', $working_staff_hours['afternoon_endtime']->toTimeString())[0], explode(':', $working_staff_hours['afternoon_endtime']->toTimeString())[1]);

                    //se è il giorno stesso oppure stiamo elaborando il primo o l'ultimo giorno della richiesta
                    if ($first_day == $last_day || $first_day === $attendance_day_f || $last_day === $attendance_day_f) {
                        if ($d_start >= $lunch_time_start && $d_start < $lunch_time_end)
                            $d_start = $lunch_time_start;
                        if ($d_start < $morning_start)
                            $d_start = $morning_start;
                        if ($d_start >= $afternoon_end)
                            $d_start = $afternoon_end;
                        if ($d_end < $morning_start)
                            $d_end = $morning_start;
                        if ($d_end >= $lunch_time_start && $d_end < $lunch_time_end)
                            $d_end = $lunch_time_end;
                        if ($d_end >= $afternoon_end)
                            $d_end = $afternoon_end;

                        $attendance_time += $d_start->diffInMinutes($d_end);

                        if ($d_start <= $lunch_time_start && $d_end >= $lunch_time_end)
                            $attendance_time -= $mins_lunch_time;
                    } else {
                        $attendance_time += $mins_working_time;
                    }
                }
            }
        }

        return $attendance_time;
    }

    public function calcAttendanceHours($date_start, $date_end, $staff_id = null) {
        return ($this->calcAttendanceMinutes($date_start, $date_end, $staff_id) / 60);
    }

    //verifica che nell'intervallo della richiesta d'assenza non sia già presente un'altra richiesta
    public function isAttendanceAlreadyPresent($date_start, $date_end, $compare_method = 'datetime', $staff_id = null, $id = null, $attendance_type=null) {
        $attendances = Attendance::where(function ($q) use ($date_start, $date_end, $compare_method) {
            //se [$compare_method = 'date'] => check solo su data
            //se [$compare_method = 'datetime'] => check su data+orari
            switch ($compare_method) {
                case 'date':
                    $q->where(function ($q1) use ($date_start, $date_end) {
                        $q1->whereDate('date_start', '>=', format_date_for_db($date_start));
                        $q1->whereDate('date_start', '<=', format_date_for_db($date_end));
                    });
                    $q->orWhere(function ($q2) use ($date_start, $date_end) {
                        $q2->whereDate('date_end', '>=', format_date_for_db($date_start));
                        $q2->whereDate('date_end', '<=', format_date_for_db($date_end));
                    });
                    $q->orWhere(function ($q3) use ($date_start, $date_end) {
                        $q3->whereDate('date_start', '<=', format_date_for_db($date_start));
                        $q3->whereDate('date_end', '>=', format_date_for_db($date_end));
                    });
                    break;
                case 'datetime':
                    $q->where(function ($q1) use ($date_start, $date_end) {
                        $q1->where('date_start', '>=', format_date_for_db($date_start));
                        $q1->where('date_start', '<=', format_date_for_db($date_end));
                    });
                    $q->orWhere(function ($q2) use ($date_start, $date_end) {
                        $q2->where('date_end', '>=', format_date_for_db($date_start));
                        $q2->where('date_end', '<=', format_date_for_db($date_end));
                    });
                    $q->orWhere(function ($q3) use ($date_start, $date_end) {
                        $q3->where('date_start', '<=', format_date_for_db($date_start));
                        $q3->where('date_end', '>=', format_date_for_db($date_end));
                    });
                    break;
            }
        })->whereNull('deleted_at');

        //se $staff_id non è valorizzato allora la ricerca è globale
        if ($attendance_type != null) {
            $attendances->whereIn('type_id', $attendance_type);
        }

        //se $staff_id non è valorizzato allora la ricerca è globale
        if ($staff_id != null) {
            $attendances->where('staff_id', $staff_id);
        }

        //se $id allora escludo dal check la richiesta con questo id
        if ($id != null) {
            $attendances->where('id', '!=', $id);
        }

        return ($attendances->ViewActiveStaff()->count()) ? true : false;
    }

    //splitta una richiesta d'assenza a cavallo di uno o più mesi in più date mensili tenendo conto degli orari del dipendente
    public function splitAttendance($date_start, $date_end, $staff_id, $diff_months = null) {
        $splitted_dates = [];

        //salvo le date originali
        $rd_date_start = $date_start;
        $rd_date_end = $date_end;

        if ($diff_months == null) {
            $diff_months = $date_start->copy()->floorMonth()->diffInMonths($date_end->copy()->floorMonth());
            $diff_months--;
        }

        //recupero gli orari del dipendente
        $staff_employee = Staff::findOrFail($staff_id);
        $work_start_day_hour = $staff_employee['morning_starttime'];
        $work_end_day_hour = $staff_employee['afternoon_endtime'];

        //richiesta con prima data
        $date_end = $date_start->copy()->endOfMonth()->setTime(explode(':', $work_end_day_hour)[0], explode(':', $work_end_day_hour)[1]);
        $splitted_dates[] = [
            'date_start' => $rd_date_start,
            'date_end' => $date_end,
            'hours' => $this->calcAttendanceHours($date_start, $date_end, $staff_id),
        ];

        //calcolo le N richieste, tante quante sono i mesi di differenza tra le due date -inizio -fine
        while ($diff_months > 0) {
            $date_start = $date_start->copy()->addMonths(1)->startOfMonth()->setTime(explode(':', $work_start_day_hour)[0], explode(':', $work_end_day_hour)[1]);
            $date_end = $date_start->copy()->endOfMonth()->setTime(explode(':', $work_end_day_hour)[0], explode(':', $work_end_day_hour)[1]);
            $splitted_dates[] = [
                'date_start' => $date_start,
                'date_end' => $date_end,
                'hours' => $this->calcAttendanceHours($date_start, $date_end, $staff_id),
            ];
            $diff_months--;
        }

        //richiesta con ultima data
        $date_start = $date_start->copy()->addMonths(1)->startOfMonth()->setTime(explode(':', $work_start_day_hour)[0], explode(':', $work_end_day_hour)[1]);
        $splitted_dates[] = [
            'date_start' => $date_start,
            'date_end' => $rd_date_end,
            'hours' => $this->calcAttendanceHours($date_start, $rd_date_end, $staff_id),
        ];

        return $splitted_dates;
    }

    /**
     * Verifica se un'intervallo è uno straordinario valido
     * ritorna true in caso positivo
     * @param $date_start
     * @param $date_end
     * @param null $staff_id
     * @return boolean
     */
    public function isIntervalValidOvertime($date_start, $date_end, $staff_id = null): bool
    {
        if($this->isHoliday($date_start,$date_end))
            return true;

        $staff = Staff::findOrFail($staff_id);
        $morning_starttime = Carbon::parse($staff->morning_starttime)->format('His');
        $morning_endtime = Carbon::parse($staff->morning_endtime)->format('His');
        $afternoon_starttime = Carbon::parse($staff->afternoon_starttime)->format('His');
        $afternoon_endtime = Carbon::parse($staff->afternoon_endtime)->format('His');

        $start_time = Carbon::parse($date_start)->format('His');
        $end_time = Carbon::parse($date_end)->format('His');

        //straordinario mattutino
        if($start_time<$morning_starttime && $end_time<=$morning_starttime)
            return true;
        //pausa pranzo saltata
        if($start_time<$afternoon_starttime && $end_time<=$afternoon_starttime && $start_time>=$morning_endtime)
            return true;
        //straordinario oltre chiusura
        if($start_time>=$afternoon_endtime)
            return true;
        return false;
    }
    /**
     * Controlla se la data è un festivo/chiusura/weekend
     * ritorna true in caso positivo
     * @param $date_start
     * @param $date_end
     * @return boolean
     */
    public function isHoliday($date_start,$date_end): bool
    {
        //controllo weekend
        if(Carbon::parse($date_start)->isWeekend())
            return true;
        //controllo chiusura aziendale
        if($this->isAttendanceAlreadyPresent($date_start,$date_end,"datetime",null,null,[AttendanceType::CLOSURE_TYPE_ID]))
            return true;
        return false;
        //TODO valutare se fare un check sui festivi: $this->getHolidaysDates(), ciclare e confrontare con date_start e date_end
    }
}
