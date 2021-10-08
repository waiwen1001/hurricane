<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDeliveryDateTimeToDriverJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_jobs', function (Blueprint $table) {
            $table->dropColumn(['delivery_date_time']);
            $table->dateTime('est_delivery_from')->after('address')->nullable();
            $table->dateTime('est_delivery_to')->after('est_delivery_from')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_jobs', function (Blueprint $table) {
            $table->dateTime('delivery_date_time');
            $table->dropColumn(['est_delivery_from', 'est_delivery_to']);
        });
    }
}
