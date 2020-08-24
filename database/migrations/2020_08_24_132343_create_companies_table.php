<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
          $table->id();
          $table->integer('industry_id');
          $table->string('cin');
          $table->string('company_name');
          $table->string('class');
          $table->string('status');
          $table->date('date_of_incorporation');
          $table->string('registration_number');
          $table->string('category');
          $table->string('sub_category');
          $table->string('roc_code');
          $table->integer('no_of_members');
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
        Schema::dropIfExists('companies');
    }
}
