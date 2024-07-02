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
        $rules = config('validate.message.rules');
        $validator = Validator::make($request->all(), $rules);

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
        return response()->json($tasks);
    }
}
