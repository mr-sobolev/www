<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:publish {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish a message to RabbitMQ';

    /**
     * Execute the console command.
     */
    public function handle(RabbitMQService $rabbitMQService)
    {
        $message = $this->argument('message');
        $rabbitMQService->sendMessage($message);
    }
}
