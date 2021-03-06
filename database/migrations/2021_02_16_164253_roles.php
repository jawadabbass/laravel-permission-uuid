<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Roles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {

            $table->uuid('id')->primary()->unique();
            $table->uuid('created_by_user_id')->nullable();
            $table->uuid('created_by_company_id')->nullable();
            $table->string('title')->nullable();
            $table->integer('sort_order')->default(999999);
            $table->enum('status', ['active', 'inactive'])->default('active')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
