<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('document', 50)->unique();
            $table->string('email', 100)->unique();
            $table->integer('people_type')->default(\App\Enums\TypePeopleEnum::F->value);

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('client_contact', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade');

            $table->string('prefix_international')->default(55);
            $table->string('prefix');
            $table->string('number');
            $table->string('name')->nullable();

            $table->timestamps();
        });

        Schema::create('client_address', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('cascade');

            $table->string('zipcode');
            $table->string('street');
            $table->string('state');
            $table->string('city');
            $table->string('district');
            $table->text('complement')->nullable();
            $table->string('number')->default("S/N")->nullable();
            $table->boolean("main")->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
        Schema::dropIfExists('client_contact');
    }
};
