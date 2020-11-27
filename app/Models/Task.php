<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'task_id',
        'name',
        'active',
        'completed',
        'completionDate',
        'dropDate',
        'dueDate',
        'estimatedMinutes',
        'flagged',
        'inInbox',
        'note',
        'project',
        'taskStatus'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'completionDate' => 'datetime',
        'dropDate' => 'datetime',
        'dueDate' => 'datetime',
    ];
}
