<?php

namespace Tests\Unit;

use Tests\TestCase;
use Facades\Tests\Setup\ContentFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\ContentSubscription;
use App\Content;

class ContentTest extends TestCase
{
    use RefreshDatabase;
    
    
    /** @test */
    public function it_has_a_owner() {
        $content = ContentFactory::create();
        
        $this->assertInstanceOf('App\User', $content->owner()->first());
    }
    
     /** @test */
    public function a_content_can_have_a_subscriptions() {
        ///$this->withoutExceptionHandling();
        
        $content = ContentFactory::create();
        
        $curriculum = new \App\Curriculum;
        
        $subscription = ContentSubscription::create($attributes = [
            'content_id' => $content->id,
            
            'subscribable_type' => get_class($curriculum),
            'subscribable_id' => 1,
            'sharing_level_id' => 1,

            'visibility' => true,
            'owner_id' => 1,
        ]);
        
        
        $c = Content::findOrFail($content->id);
        
        $this->assertInstanceOf('App\ContentSubscription', $c->subscriptions()->first());
    }
    
}
