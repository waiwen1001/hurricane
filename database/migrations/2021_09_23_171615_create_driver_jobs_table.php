<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->longText('address')->nullable();
            $table->dateTime('delivery_date_time')->nullable();
            $table->integer('pick_up_id')->nullable();
            $table->string('pick_up')->nullable();
            $table->string('driver')->nullable();
            $table->integer('driver_id')->nullable();
            $table->dateTime('driver_accepted_at')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('status_updated_at')->nullable();
            $table->longText('remarks')->nullable();
            $table->integer('inactive')->nullable();
            $table->date('job_date')->nullable();
            $table->string('created_by')->nullable();
            $table->integer('created_by_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('driver_jobs');
    }
}
