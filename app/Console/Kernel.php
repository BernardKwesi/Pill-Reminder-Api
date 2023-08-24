<?php

namespace App\Console;

use App\Models\Dosage;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//         $schedule->command('inspire')->everyMinute();

        $schedule->call(function (Schedule $schedule) {
            $pillReminders = Dosage::all();
            $currentDateTime = Carbon::now();
            foreach ($pillReminders as $pillReminder) {

                $dosageTimes =json_decode($pillReminder->dosage_times, true);


                foreach ($dosageTimes as $dosageTime) {
                    if ($dosageTime === $currentDateTime->format('H:i')) {
                        Log::info("Reminder:". $dosageTime);

                        $nextDosageTime = Carbon::parse($dosageTime);

                        if ($pillReminder->dosage_frequency === 'weekly') {
                            $nextDosageTime->addWeek();
                        } elseif ($pillReminder->dosage_frequency === 'daily') {
                            $nextDosageTime->addDay();
                        } elseif ($pillReminder->dosage_frequency === 'monthly') {
                            $nextDosageTime->addMonth();
                        }

                        // Calculate interval between current time and dosage time
                        $timeUntilDosage = $nextDosageTime->diffInMinutes(Carbon::now());

                        $user = $pillReminder->user->name;
                        $pillName = $pillReminder->pill_name;

                        // Schedule the notification

                        Log::info("Scheduling notification $user : $pillName");
                        // Send notification to Firebase
                        // Include FCM logic here
                    }
                }
            }
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
