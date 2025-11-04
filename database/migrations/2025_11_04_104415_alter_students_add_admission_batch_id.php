<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterStudentsAddAdmissionBatchId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if(!Schema::hasColumn('students', 'admission_batch_id')){
            Schema::table('students', function(Blueprint $table){
                $table->bigInteger('admission_batch_id')->nullable();
                $table->boolean('reg_payment_status')->default(0);
                $table->bigInteger('card_payment_transaction_id')->nullable();
                $table->bigInteger('card_payment_year_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
