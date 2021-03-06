<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Curriculum;
use App\Grade;
use App\Subject;
use App\Organization;
use App\OrganizationType;
use App\TerminalObjective;
use App\EnablingObjective;
use App\Medium;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use DOMDocument;
use App\Content;
use App\Glossar;
use App\Group;
use App\Country;
use App\State;
use App\User;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\DB;

class CurriculumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort_unless(\Gate::allows('curriculum_access'), 403);

        $curricula = Curriculum::where('owner_id', auth()->user()->id)->get();
       
        if (request()->wantsJson()){    
            return ['curricula' => $curricula];
        }
        return view('curricula.index')
          ->with(compact('curricula'));
    }
    
    public function list()
    {
        abort_unless(\Gate::allows('curriculum_access'), 403);
        
        if (auth()->user()->role()->id == 1)
        {
            $curricula = Curriculum::select([
            'id', 
            'title', 
            'state_id',
            'country_id',
            'grade_id',
            'subject_id',
            'organization_type_id',
            'owner_id',
            ]);
        } else {
             $curricula = Curriculum::select([
            'id', 
            'title', 
            'state_id',
            'country_id',
            'grade_id',
            'subject_id',
            'organization_type_id',
            'owner_id',
            ])->where('owner_id', auth()->user()->id);
        }
       
        
        return DataTables::of($curricula)
            ->addColumn('state', function ($curricula) {
                return isset($curricula->state->lang_de) ? $curricula->state->lang_de : '-';                
            })
            ->addColumn('country', function ($curricula) {
                return $curricula->country->lang_de;                
            })
            ->addColumn('grade', function ($curricula) {
                return $curricula->grade->title;                
            })
            ->addColumn('subject', function ($curricula) {
                return $curricula->subject->title;
            })
            ->addColumn('organizationtype', function ($curricula) {
                return $curricula->organizationType->title;
            })
            ->addColumn('owner', function ($curricula) {
                return $curricula->owner->firstname.' '.$curricula->owner->lastname;                
            })
            ->addColumn('action', function ($curricula) {
                 $actions  = '';

                    if (\Gate::allows('curriculum_edit') AND ($curricula->owner_id == auth()->user()->id)){
                        $actions .= '<a href="'.route('curricula.edit', $curricula->id).'" '
                                    . 'class="btn">'
                                    . '<i class="fa fa-pencil-alt"></i>'
                                    . '</a>';
                    }
                    if (\Gate::allows('curriculum_edit') AND ($curricula->owner_id == auth()->user()->id)){
                        $actions .= '<a href="'.route('curricula.editOwner', $curricula->id).'" '
                                    . 'class="btn">'
                                    . '<i class="fa fa-user"></i>'
                                    . '</a>';
                    }
                    if (\Gate::allows('curriculum_delete') AND ($curricula->owner_id == auth()->user()->id)){
                        $actions .= '<button type="button" '
                                . 'class="btn text-danger" '
                                . 'onclick="destroyDataTableEntry(\'curricula\','.$curricula->id.')">'
                                . '<i class="fa fa-trash"></i></button>';
                    }
              
                return $actions;
            })
           
            ->addColumn('check', '')
            ->setRowId('id')
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_unless(\Gate::allows('curriculum_create'), 403);
        
        $grades = Grade::all();
        $subjects   = Subject::all();
        $organization_types = OrganizationType::all();
        
        $countries = Country::all();
        $states = State::where('country', 'DE')->get();
        
        return view('curricula.create')
                ->with(compact('grades'))
                ->with(compact('subjects'))
                ->with(compact('countries'))
                ->with(compact('states'))
                ->with(compact('organization_types'))
                ;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('curriculum_create'), 403);
        $input = $this->validateRequest();
         
        $curriculum = Curriculum::firstOrCreate([
            'title'                 => $input['title'],
            'description'           => $input['description'],
            'author'                => $input['author'],
            'publisher'             => $input['publisher'],
            'city'                  => $input['city'],
            'date'                  => $input['date'],
            'color'                 => $input['color'],
            'grade_id'              => format_select_input($input['grade_id']),
            'subject_id'            => format_select_input($input['subject_id']),
            'organization_type_id'  => format_select_input($input['organization_type_id']),
            'state_id'              => format_select_input($input['state_id']),
            'country_id'            => format_select_input($input['country_id']),
            'medium_id'             => $this->getMediumIdByInputFilepath($input),
            'owner_id'              => auth()->user()->id,
            
        ]);
        
        // axios call? 
        if (request()->wantsJson()){    
            return ['message' => $curriculum->path()];
        }
        
        return redirect($curriculum->path());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Curriculum  $curriculum
     * @return \Illuminate\Http\Response
     */
    public function show(Curriculum $curriculum, $achievements = false)
    { 
        abort_unless(\Gate::allows('curriculum_show'), 403);
        //check if user is enrolled or admin -> else 403 
        
        abort_unless((auth()->user()->curricula()->contains('id', $curriculum->id) // user enrolled
                  OR ($curriculum->owner_id == auth()->user()->id )                // or owner
                  OR (auth()->user()->currentRole()->first()->id == 1)            // or admin
                  OR ((env('GUEST_USER') != null) ? User::find(env('GUEST_USER'))->curricula()->contains('id', $curriculum->id) : false) //or allowed via guest
                ), 403);     // or admin
        
        $objectiveTypes = \App\ObjectiveType::all();
        $levels = \App\Level::all();
        
        $curriculum = Curriculum::with(['terminalObjectives', 
                        'terminalObjectives.media', 
                        'terminalObjectives.mediaSubscriptions', 
//                        'terminalObjectives.referenceSubscriptions.siblings.referenceable', 
//                        'terminalObjectives.quoteSubscriptions.siblings.quotable', 
                        'terminalObjectives.achievements' => function($query) {
                            $query->where('user_id', auth()->user()->id);
                        },
                        'terminalObjectives.enablingObjectives', 
                        'terminalObjectives.enablingObjectives.media',
                        'terminalObjectives.enablingObjectives.mediaSubscriptions', 
//                        'terminalObjectives.enablingObjectives.referenceSubscriptions.siblings.referenceable', 
//                        'terminalObjectives.enablingObjectives.quoteSubscriptions.siblings.quotable', 
                        'terminalObjectives.enablingObjectives.achievements' => function($query) {
                            $query->where('user_id', auth()->user()->id);
                        },        
                        'contentSubscriptions.content', 
                        'glossar.contents', 
                        'media'])
                        ->find($curriculum->id);
        $settings= json_encode([
            'edit' => true,
            'cross_reference_curriculum_id' => false
        ]);
        
        return view('curricula.show')
                ->with(compact('curriculum'))
                ->with(compact('objectiveTypes'))
                ->with(compact('levels'))
                ->with(compact('settings'));
    }
    /**
     * Display the specified resource with achievements.
     *
     * @param  \App\Curriculum  $curriculum
     * @return \Illuminate\Http\Response
     */
    public function showAchievements(Curriculum $curriculum)
    {
        $this->show($curriculum, true);
    }
    
    public function getObjectives(Curriculum $curriculum)
    {
        $curriculum = Curriculum::where('id', $curriculum->id)->with('terminalObjectives.enablingObjectives')->get()->first();
        if (request()->wantsJson()){    
            return ['curriculum' => $curriculum];
        }
    }
    /**
     * Get achievements
     *
     * @param  \App\Curriculum  $curriculum
     * @return \Illuminate\Http\Response
     */
    public function getAchievements(Curriculum $curriculum)
    {
        abort_unless(\Gate::allows('curriculum_show'), 403);
        //check if user is enrolled or admin -> else 403 
        abort_unless((auth()->user()->curricula()->contains('id', $curriculum->id) // user enrolled
                  OR (auth()->user()->currentRole()->first()->id == 1)), 403);     // or admin
        $user_ids = request()->user_ids;
        
        $curriculum = Curriculum::with(['terminalObjectives', 
                        'terminalObjectives.achievements' => function($query) use ($user_ids) {
                                                            $query->whereIn('user_id', $user_ids);
                                                        },
                        'terminalObjectives.enablingObjectives', 
                        'terminalObjectives.enablingObjectives.achievements' => function($query) use ($user_ids) {
                                                            $query->whereIn('user_id', $user_ids);
                                                        },  
                        ])
                        ->find($curriculum->id);
        if (request()->wantsJson()){     
            return ['curriculum' => $curriculum];
        }
       
    }

    /**
     * Show curriculum in edit mode
     *
     * @param  \App\Curriculum  $curriculum
     * @return \Illuminate\Http\Response
     */
    public function edit(Curriculum $curriculum)
    {
        $grades             = Grade::all();
        $subjects           = Subject::all();
        $organization_types = OrganizationType::all();
        
        $countries = Country::all();
        $states = State::all();
        
        return view('curricula.edit')
                ->with(compact('grades'))
                ->with(compact('subjects'))
                ->with(compact('organization_types'))
                ->with(compact('countries'))
                ->with(compact('states'))
                ->with(compact('curriculum'));
    }
    
    /**
     * Show edit_owner
     *
     * @param  \App\Curriculum  $curriculum
     * @return \Illuminate\Http\Response
     */
    public function editOwner(Curriculum $curriculum)
    {
        $users = Organization::where('id', auth()->user()->current_organization_id)->get()->first()->users()->get();
        
        return view('curricula.owner')
                ->with(compact('curriculum'))
                ->with(compact('users'));
    }
    /**
     * Store edit_owner
     *
     * @param  \App\Curriculum  $curriculum
     * @return \Illuminate\Http\Response
     */
    public function storeOwner(Request $request, Curriculum $curriculum)
    {
        abort_unless(\Gate::allows('curriculum_edit'), 403);
        $input = $this->validateRequest();

        $curriculum->update([
                'owner_id' => format_select_input($input['owner_id'])
            ]);
        
        return redirect('/curricula');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Curriculum  $curriculum
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Curriculum $curriculum)
    {
        abort_unless(\Gate::allows('curriculum_edit'), 403);
        
        $input = $this->validateRequest();
        
        $curriculum->update([
            'title'                 => $input['title'],
            'description'           => $input['description'],
            'author'                => $input['author'],
            'publisher'             => $input['publisher'],
            'city'                  => $input['city'],
            'date'                  => $input['date'],
            'color'                 => $input['color'],
            'grade_id'              => format_select_input($input['grade_id']),
            'subject_id'            => format_select_input($input['subject_id']),
            'organization_type_id'  => format_select_input($input['organization_type_id']),
            'state_id'              => isset($input['state_id']) ? format_select_input($input['state_id']) : null, 
            'country_id'            => format_select_input($input['country_id']),
            'medium_id'             => $this->getMediumIdByInputFilepath($input),
            'owner_id'              => auth()->user()->id,
        ]);
        
        return redirect($curriculum->path());
    }
    
    /**
     * If $input['filepath'] is set and medium exists, id is return, else return is null
     * @param array $input
     * @return mixed
     */
    public function getMediumIdByInputFilepath($input){
        if (isset($input['filepath']))
        {
            $medium = new Medium();
            return (null !== $medium->getByFilemanagerPath($input['filepath'])) ? $medium->getByFilemanagerPath($input['filepath'])->id : null;
        } 
        else
        {
            return null;
        }
    }
    
    public function enrol()
    {
        abort_unless(\Gate::allows('course_create'), 403);
        
        foreach ((request()->enrollment_list) AS $enrolment)
        {  
            $return[] = Group::findOrFail($enrolment['group_id'])->curricula()->syncWithoutDetaching($enrolment['curriculum_id']); 
        }
        
        return $return;  
    }
    
     public function expel()
    {
        abort_unless(\Gate::allows('course_create'), 403);
        
        foreach ((request()->expel_list) AS $expel)
        {  
            $group = Group::find($expel['group_id']);
            $return[] = $group->curricula()->detach($expel['curriculum_id']);
        }
        
        return $return;  
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Curriculum  $curriculum
     * @return \Illuminate\Http\Response
     */
    public function destroy(Curriculum $curriculum)
    {
        abort_unless(\Gate::allows('curriculum_delete'), 403);
        
        // detach groups
        $curriculum->groups()->detach();
        
        // delete certificates
        foreach ($curriculum->certificates AS $certificate)
        {
            (new CertificateController)->destroy($certificate);
        }
        
        
        
        foreach ($curriculum->enablingObjectives AS $ena)
        {
            (new EnablingObjectiveController)->destroy($ena);
        }
        
        foreach ($curriculum->terminalObjectives AS $ter)
        {
            (new TerminalObjectiveController)->destroy($ter);   
        }
        
        //  delete glossar
        $curriculum->glossar()->delete();
       
        // delete mediaSubscriptions -> media will not be deleted
        $curriculum->mediaSubscriptions()
                ->where('subscribable_type', '=', 'App\Curriculum')
                ->where('subscribable_id', '=', $curriculum->id)
                ->delete();
        
        // delete navigator_items
        $curriculum->navigator_item()
                ->where('referenceable_type', '=', 'App\Curriculum')
                ->where('referenceable_id', '=', $curriculum->id)
                ->delete();
        
        // delete contents 
        foreach ($curriculum->contents AS $content)
        {
            (new ContentController)->destroy($content, 'App\Curriculum', $curriculum->id); // delete or unsubscribe if content is still subscribed elsewhere
        }
        
        $return = $curriculum->delete();
        
        
        //todo check/delete unrelated references(in references table)
        if (request()->wantsJson()){    
            return ['message' => $return];
        }
     //   return back();
    }
    
    protected function validateRequest()
    {               
        
        return request()->validate([
            'title'                 => 'sometimes',
            'description'           => 'sometimes',
            'author'                => 'sometimes',
            'publisher'             => 'sometimes',
            'city'                  => 'sometimes',
            'date'                  => 'sometimes',
            'color'                 => 'sometimes',
            'grade_id'              => 'sometimes',
            'subject_id'            => 'sometimes',
            'organization_type_id'  => 'sometimes',
            'state_id'              => 'sometimes',
            'country_id'            => 'sometimes',
            'filepath'              => 'sometimes',
            'owner_id'              => 'sometimes',
            ]);
    }
}
