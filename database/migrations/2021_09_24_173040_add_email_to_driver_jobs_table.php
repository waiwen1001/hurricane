<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailToDriverJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_jobs', function (Blueprint $table) {
            $table->string('email')->after('name')->nullable();
            $table->string('contact_number')->after('email')->nullable();
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
            $table->dropColumn(['email', 'contact_number']);
        });
    }
}