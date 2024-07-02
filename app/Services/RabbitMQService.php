<?php
// app/Services/RabbitMQService.php

namespace App\Services;

use Enqueue\AmqpLib\AmqpConnectionFactory;
use Enqueue\AmqpLib\AmqpContext;
use Enqueue\AmqpLib\AmqpMessage;
use Interop\Amqp\AmqpTopic;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\Impl\AmqpBind;

class RabbitMQService
{
    protected $connectionFactory;

    public function __construct()
    {
        $this->connectionFactory = new AmqpConnectionFactory([
            'host' => env('RABBITMQ_HOST', 'localhost'),
            'port' => env('RABBITMQ_PORT', 5672),
            'vhost' => env('RABBITMQ_VHOST', '/'),
            'user' => env('RABBITMQ_USER', 'guest'),
            'pass' => env('RABBITMQ_PASS', 'guest'),
            'persisted' => false,
        ]);
    }

    public function createContext(){
      return  $this->connectionFactory->createContext();
    }

    public function sendMessage(string $messageBody)
    {
        try{
            $context = $this->createContext();
            
            $tasksTopic = $context->createTopic('tasks');
            $tasksTopic->setType(AmqpTopic::TYPE_FANOUT);
            $context->declareTopic($tasksTopic);

            $tasksQueue = $context->createQueue('tasks');
            $tasksQueue->addFlag(AmqpQueue::FLAG_DURABLE);
            $context->declareQueue($tasksQueue);

            $context->bind(new AmqpBind($tasksTopic, $tasksQueue));
            $message = $context->createMessage($messageBody);

            $context->createProducer()->send($tasksTopic, $message);

            $context->close();
            $this->info("Message sent to topic '{tasks}': '{$this->message}'");
        }catch(Exception $err){
            $this->error("Throw '{error}': '$err->getMessage()', '{$messageBody}' not send");
        }
    }
}

?>