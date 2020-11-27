<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_id', 15)->unique();
            $table->text('name');
            $table->boolean('active');
            $table->boolean('completed');
            $table->dateTimeTz('completionDate')->nullable();
            $table->dateTimeTz('dropDate')->nullable();
            $table->dateTimeTz('dueDate')->nullable();
            $table->integer('estimatedMinutes')->nullable();
            $table->boolean('flagged');
            $table->boolean('inInbox');
            $table->text('note')->nullable();
            $table->text('project')->nullable();
            $table->string('taskStatus', 15);
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
        Schema::dropIfExists('tasks');
    }
}
