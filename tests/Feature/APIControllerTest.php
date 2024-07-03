<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class APIControllerTest extends TestCase
{
   /** @test */
   public function it_validates_incoming_data()
   {
       $response = $this->postJson('/api/v1/tasks', []);

       $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJsonValidationErrors(['title', 'content']);
   }

   /** @test */
   public function it_queues_message_to_rabbitmq()
   {
       $data = [
           'title' => 'Test Title',
           'content' => 'Test Content',
           'is_done' => true,
       ];

       $response = $this->postJson('/api/v1/tasks', $data);

       $response->assertStatus(Response::HTTP_OK)
                ->assertJson(['status' => 'Message queued for saving']);
   }


}
