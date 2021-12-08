<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssignedAtToDriverJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_jobs', function (Blueprint $table) {
            $table->dateTime('assigned_at')->after('status_updated_at')->nullable();
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
            $table->dropColumn(['assigned_at']);
        });
    }
}
