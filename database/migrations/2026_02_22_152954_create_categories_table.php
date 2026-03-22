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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // ✅ FIX: user_id যোগ করা হয়েছে — প্রতিটি category একজন user এর
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // user delete হলে তার categories ও delete হবে

            $table->string('name');         // user যে নাম দেবে
            $table->string('slug');         // ✅ FIX: unique() সরানো হয়েছে
                                            // কারণ দুই আলাদা user এর same slug হতে পারে
                                            // unique হবে user_id+slug কম্বিনেশনে
            $table->boolean('is_expense')->default(true);
            $table->string('icon')->default('category')->nullable(); // Material icon name
            $table->timestamps();

            // ✅ FIX: global unique নয় — একই user এর মধ্যে slug unique হবে
            $table->unique(['user_id', 'slug']); // user_id + slug কম্বো unique
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
