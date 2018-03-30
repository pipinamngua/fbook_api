<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNameJpAndEnToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_en', 100)->after('name');
            $table->string('name_jp', 100)->after('name_en');
            $table->renameColumn('name', 'name_vi');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('name_en');
            $table->dropColumn('name_jp');
            $table->renameColumn('name_vi', 'name');
        });
    }
}
