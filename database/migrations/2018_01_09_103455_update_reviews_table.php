<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('title');
            $table->longText('content')->change();
            $table->integer('up_vote')->default(0);
            $table->integer('down_vote')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('content')->change();
            $table->dropColumn('title');
            $table->dropColumn('up_vote');
            $table->dropColumn('down_vote');
        });
    }
}
