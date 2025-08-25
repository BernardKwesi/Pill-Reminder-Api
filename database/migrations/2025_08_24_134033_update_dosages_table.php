<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDosagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::table('dosages', function (Blueprint $table) {
            // New fields being added
            $table->integer("updated_quantity")->nullable()->after("start_date");
            $table->integer("medication_quantity")->nullable()->after("updated_quantity");
            $table->integer("quantity_per_dose")->nullable()->after("medication_quantity");
            $table->string("dosage_interval")->nullable()->after("dosage_frequency");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
           Schema::table('dosages', function (Blueprint $table) {
            $table->dropColumn([
                "updated_quantity",
                "medication_quantity",
                "quantity_per_dose",
                "dosage_interval",
            ]);
        });
    }
}
