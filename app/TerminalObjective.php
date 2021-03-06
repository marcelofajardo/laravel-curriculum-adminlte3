<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TerminalObjective extends Model
{
    protected $fillable = [ 'title', 
                            'description',
                            'order_id', 
                            'color', 
                            'time_approach',
                            'curriculum_id',
                            'objective_type_id',
                            'visibility',
                          ];
    protected $casts = [
        'visibility' => 'boolean',
        'referencing_curriculum_id' => 'object'
    ];

    public function path(){
        return "/curricula/{$this->curriculum_id}";
    }
    
    public function type()
    {
        return $this->belongsTo('App\ObjectiveType', 'id', 'objective_type_id');
    }
    
    public function enablingObjectives()
    {
        return $this->hasMany('App\EnablingObjective', 'terminal_objective_id', 'id');
    }
    
    public function contentSubscriptions()
    {
        return $this->morphMany('App\ContentSubscription', 'subscribable');
    }
    
    public function contents()
    {
        return $this->hasManyThrough(
            'App\Content',
            'App\ContentSubscription',
            'subscribable_id', // Foreign key on content_subscription table...
            'id', // Foreign key on content table...
            'id', // Local key on terminal objectives table...
            'content_id' // Local key on content_subscription table...
        )->where('subscribable_type', get_class($this)); 
    }
    
    public function curriculum()
    {
        return $this->belongsTo('\App\Curriculum', 'curriculum_id', 'id');
    }
    
    public function mediaSubscriptions()
    {
        return $this->morphMany('App\MediumSubscription', 'subscribable');
    }
    
    public function media()
    {
        return $this->hasManyThrough(
            'App\Medium',
            'App\MediumSubscription',
            'subscribable_id', // Foreign key on medium_subscription table...
            'id', // Foreign key on medium table...
            'id', // Local key on terminal objectives table...
            'medium_id' // Local key on medium_subscription table...
        )->where('subscribable_type', get_class($this)); 
    }
    
    public function references()
    {
        return $this->hasManyThrough(
            'App\Reference',
            'App\ReferenceSubscription',
            'referenceable_id', // Foreign key on reference_subscription table...
            'id', // Foreign key on reference table...
            'id', // Local key on terminal_objectives table...
            'reference_id' // Local key on reference_subscription table...
        )->where('referenceable_type', get_class($this)); 
    }
    
    public function referenceSubscriptions()
    {
        return $this->morphMany('App\ReferenceSubscription', 'referenceable');
    }
    
    public function repositorySubscriptions()
    {
        return $this->morphMany('App\RepositorySubscription', 'subscribable');
    }
    
    public function subscriptions()
    {
        return $this->hasMany(TerminalObjectiveSubscriptions::class);
    }
    
    public function quoteSubscriptions()
    {
        return $this->morphMany('App\QuoteSubscription', 'quotable');
    }
    
    public function achievements()
    {
        return $this->morphMany('App\Achievement', 'referenceable');
    }
    
    public function progresses()
    {
        return $this->morphMany('App\Progress', 'referenceable');
    }
    
}
