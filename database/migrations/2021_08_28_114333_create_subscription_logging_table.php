<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionLoggingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_logging', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('payload')->nullable();
            $table->string('merchant_id')->nullable();
            $table->string('subscription_id')->nullable();
            $table->string('msisdn')->nullable();
            $table->string('operator_id')->nullable();
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
        Schema::dropIfExists('subscription_logging');
    }
}
