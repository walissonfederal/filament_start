<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');

            $table->string("name");

            $table->integer("necessary")
                  ->default(\App\Enums\NecessaryTransactionEnum::very_low);

            $table->integer("type");
            $table->decimal("value", 12, 2)
                  ->default(0.00);
            $table->boolean("monthly")
                  ->default(false);
            $table->date("date_initial_monthly")->nullable();
            $table->date("date_finish_monthly")->nullable();
            $table->string("due_day", 2);
            $table->string("payment_day", 2)->nullable();
            $table->boolean("paid")->nullable();
            $table->string("current_month", 7);
            $table->string('recipient')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
