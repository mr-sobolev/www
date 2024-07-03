<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use Interop\Amqp\AmqpConsumer;
use Interop\Amqp\AmqpMessage;
use Interop\Amqp\AmqpQueue;
use Interop\Queue\Message;
use Interop\Queue\Consumer;
use App\Jobs\SaveMessageInDatabase;

class ConsumeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rabbitmq consumer';

    /**
     * Execute the console command.
     */
    public function handle(RabbitMQService $rabbitMQService)
    {
         // Check if pcntl functions are available
         if (!function_exists('pcntl_signal')) {
            $this->error('PCNTL functions are not available. This script requires PCNTL functions to handle signals.');
            return 1;
        }
        $keepRunning = true;

        // Set up signal handler
        pcntl_signal(SIGINT, function() use (&$keepRunning) {
            $this->info('SIGINT received. Shutting down...');
            $keepRunning = false;
        });

        pcntl_signal(SIGTERM, function() use (&$keepRunning) {
            $this->info('SIGTERM received. Shutting down...');
            $keepRunning = false;
        });

        $context = $rabbitMQService->createContext();
        $queue = $context->createQueue('tasks');
        $tasksConsumer = $context->createConsumer($queue);
        $subscriptionConsumer = $context->createSubscriptionConsumer();
        $this->info(" [*] Waiting for messages. To exit press CTRL+C\n");
        while($keepRunning){
            $subscriptionConsumer->subscribe($tasksConsumer, function(Message $message, Consumer $consumer) {
                $data = $message->getBody();
                $this->info("Income message, ['message' => $data]");
                SaveMessageInDatabase::dispatch($data);
                $consumer->acknowledge($message);
                return true;
            });
            // Sleep for a short period to avoid high CPU usage
            $subscriptionConsumer->consume(500);
        }

        $context->close();
        $this->info("Consumer has been gracefully shut down.\n");

    }
}
