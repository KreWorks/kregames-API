<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->uuid('linktype_id');
            $table->foreign('linktype_id')
                ->references('id')->on('linktypes')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->morphs('linkable');
            $table->string('link');
            $table->string('display_text');
            $table->boolean('visible');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('links', function (Blueprint $table) {
            $table->dropForeign('links_linktype_id_foreign');
            $table->dropMorphs('linkable');
        });
    }
};
