<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;

use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        if ($request->query('start') == null || $request->query('end') == null)
            return Task::orderBy('dueDate')->get();

        $start = strtotime($request->query('start'));
        $end = strtotime($request->query('end'));

        return Task::where(function($query) use($end) {
                $query->where('dueDate', '<=', date("Y-m-d H:i:s", $end))
                    ->where('completionDate', null);
            })
            ->orWhere(function($query) use($start, $end) {
                $query->where('completionDate', '>=', date("Y-m-d H:i:s", $start))
                    ->where('completionDate', '<=', date("Y-m-d H:i:s", $end));
            })
            ->orWhere(function($query) {
                $query->where('completionDate', null)
                    ->where(function($query) {
                        $query->where('flagged', 1)
                            ->orWhere('inInbox', 1);
                    });
            })
            ->orderBy('dueDate')
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        foreach ($request->all() as &$item) {
            $task = Task::find($item['task_id']);

            if ($task == null)
                $task = new Task;

            $task->fill($item);

            if ($task->isDirty())
                $task->save();
        }

        return ['status' => 'success'];
    }

    public function show($id)
    {
        return Task::find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
