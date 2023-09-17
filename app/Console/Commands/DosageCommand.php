<?php

namespace App\Console\Commands;

use App\Models\Dosage;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DosageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dosage:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->database = \App\Services\FirebaseService::connect();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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
                    $this->database->getReference('notifications/'.$pillReminder->user_id.'/'.time()
                    )
                        ->update([
                            "timestamp" => time(),
                             "medCode"=> "MED00".$pillReminder->id,
                             "title"=> $pillName,
                             "message" => "It is time to take your medication"
                        ]);
                }
            }
        }
    }
}
