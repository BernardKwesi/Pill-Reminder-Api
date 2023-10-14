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
    private $database;

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
        $currentDateTime = time();
        foreach ($pillReminders as $pillReminder) {

            $dosageTimes =json_decode($pillReminder->next_dosage_time, true);

            foreach($dosageTimes as $dosage){

                if($dosage == $currentDateTime){

                    $user = $pillReminder->user->name;
                    $pillName = $pillReminder->pill_name;


                    Log::info("Scheduling notification $user : $pillName  timestamp : $dosage");
                    // Send notification to Firebase
                    // Include FCM logic here
                    $this->database->getReference('notifications/'.$pillReminder->user_id.'/'.time()
                    )
                        ->update([
                            "timestamp" => time(),
                            "medCode"=> $pillReminder->id,
                            "title"=> $pillName,
                            "message" => "It is time to take your medication"
                        ]);

                    $result = $this->addDaysBasedOnFrequency($dosage, $pillReminder->dosage_frequency);
                    $updatedTimestamp = $result->timestamp;

                    // Schedule the notification
                    $position = array_search($dosage, $dosageTimes);
                    
                    $dosageTimes[$position] = $updatedTimestamp; 
                    
                    $pillReminder->update([
                        "next_dosage_time" => json_encode($dosageTimes)
                    ]);
                    
                }


            }



        }
    }



    private function addDaysBasedOnFrequency($timestamp, $frequency) {
        // Create a Carbon instance from the timestamp
        $carbon = Carbon::createFromTimestamp($timestamp);


        switch ($frequency) {
            case "once_a_week":
                return $carbon->addWeek();
            case "every_two_days":
                return $carbon->addDays(2);
            case "every_three_days":
                return $carbon->addDays(3);
            default:
                return $carbon->addDays(1);
        }
    }
}
