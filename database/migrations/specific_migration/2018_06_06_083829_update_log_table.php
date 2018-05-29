<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'string');
    }
    
    public function up()
    {
        Schema::table('log_reputations', function (Blueprint $table) {
            $table->json('pivot_data')->nullable()->default(null)->after('log_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_reputations', function (Blueprint $table) {
            $table->dropColumn('pivot_data');
        });
    }
}
