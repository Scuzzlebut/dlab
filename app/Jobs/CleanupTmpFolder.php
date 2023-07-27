<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class CleanupTmpFolder implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $startTime = microtime(true);
        Log::info('---JOB CleanupTmpFolder START ---');

        $result = File::cleanDirectory(Storage::disk('local_tmp')->path(''));

        $totalTime = microtime(true) - $startTime;
        Log::info("---FINE JOB CleanupTmpFolder, total execution time: $totalTime s ---");
    }
}
