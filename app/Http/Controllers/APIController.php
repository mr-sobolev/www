<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendMessageToRabbitMQ;
use Illuminate\Support\Facades\Validator;
use App\Models\ReadableTask;

class APIController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();
        if (isset($data['is_done'])) {
            $data['is_done'] = filter_var($data['is_done'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        $rules = config('validate.message.rules');
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $messageData = $validator->validated();
        $encodedMessage = json_encode($messageData);
        SendMessageToRabbitMQ::dispatch($encodedMessage);

        return response()->json(['status' => 'Message queued for saving']);
    }

    public function readData(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $tasks = ReadableTask::paginate($perPage);
        $tasks->getCollection()->transform(function ($task) {
            return [
                'id'    => $task->id,
                'title' => $task->title,
                'content' => $task->content,
                'is_done' => $task->is_done
            ];
        });
        return response()->json($tasks);
    }
}
