<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('display_name')->nullable();
            $table->string('name')->nullable();
            $table->string('country_code', 20)->nullable();
            $table->string('country_name')->nullable();
            $table->string('state')->nullable();
            $table->string('city_name')->nullable();
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->string('zip_code')->nullable();
            $table->decimal('star_rating', 3, 2)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->integer('room_count')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('property_category')->nullable();
            $table->string('property_sub_category')->nullable();
            $table->string('chain_code')->nullable();
            $table->text('facilities')->nullable();
            $table->text('images')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
