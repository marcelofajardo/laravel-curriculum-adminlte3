<?php

namespace App\Http\Controllers;

use App\LogbookEntry;
use Illuminate\Http\Request;

class LogbookEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('logbook_entry_create'), 403);
        $new_entry = $this->validateRequest();
        
        $entry = LogbookEntry::firstOrCreate([
            'logbook_id'=> $new_entry['logbook_id'],
            'title' => $new_entry['title'],
            'description' => $new_entry['description'],
            'begin' => $new_entry['begin'],
            'end' => $new_entry['end'],
            'owner_id' => auth()->user()->id
        ]);
        
        // axios call? 
        if (request()->wantsJson()){    
            return ['message' => $entry->path()];
        }
        
        return back();
    }
   
    /**
     * Display the specified resource.
     *
     * @param  \App\LogbookEntry  $logbookEntry
     * @return \Illuminate\Http\Response
     */
    public function show(LogbookEntry $logbookEntry)
    {
        abort_unless(\Gate::allows('logbook_show'), 403);
        // axios call? 
        if (request()->wantsJson()){   
            return [
                'message' => $logbookEntry
            ];
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LogbookEntry  $logbookEntry
     * @return \Illuminate\Http\Response
     */
    public function edit(LogbookEntry $logbookEntry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LogbookEntry  $logbookEntry
     * @return \Illuminate\Http\Response
     */
    public function update(LogbookEntry $logbookEntry)
    {
        abort_unless(\Gate::allows('logbook_edit'), 403);    
        
        $logbookEntry->update($this->validateRequest());
        
        // axios call? 
        if (request()->wantsJson()){    
            return ['message' => '/logbooks' . $logbookEntry->logbook_id];
            
        }
        return ;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LogbookEntry  $logbookEntry
     * @return \Illuminate\Http\Response
     */
    public function destroy(LogbookEntry $logbookEntry)
    {
        //delete all relations
        
        // delete mediaSubscriptions -> media will not be deleted
        $logbookEntry->mediaSubscriptions()
                ->where('subscribable_type', '=', 'App\LogbookEntry')
                ->where('subscribable_id', '=', $logbookEntry->id)
                ->delete();
        
        // delete terminalObjectiveSubscriptions
        $logbookEntry->terminalObjectiveSubscriptions()
                ->where('subscribable_type', '=', 'App\LogbookEntry')
                ->where('subscribable_id', '=', $logbookEntry->id)
                ->delete();
        
        // delete enablingObjectiveSubscriptions
        $logbookEntry->enablingObjectiveSubscriptions()
                ->where('subscribable_type', '=', 'App\LogbookEntry')
                ->where('subscribable_id', '=', $logbookEntry->id)
                ->delete();
        
        // delete contents 
        foreach ($logbookEntry->contents AS $content)
        {
            (new ContentController)->destroy($content, 'App\LogbookEntry', $logbookEntry->id); // delete or unsubscribe if content is still subscribed elsewhere
        }
        
        // delete taskSubscription
        $logbookEntry->taskSubscription()
                ->where('subscribable_type', '=', 'App\LogbookEntry')
                ->where('subscribable_id', '=', $logbookEntry->id)
                ->delete();
        
        // delete absences
        $logbookEntry->absences()
                ->where('referenceable_type', '=', 'App\LogbookEntry')
                ->where('referenceable_id', '=', $logbookEntry->id)
                ->delete();
        $return = $logbookEntry->delete();
        
        if (request()->wantsJson()){    
            return ['message' => $return];
        }
        
    }
    
    protected function validateRequest()
    {   
        return request()->validate([
            'logbook_id'        => 'sometimes|required',
            'title'             => 'sometimes|required',
            'description'       => 'sometimes',
            'begin'             => 'sometimes',
            'end'               => 'sometimes',
            'owner_id'          => 'sometimes',
        ]);
    }
    
}
