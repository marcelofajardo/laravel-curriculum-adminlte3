<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\MassUpdateUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Role;
use App\User;
use App\Organization;
use App\Group;
use App\StatusDefinition;
use App\Medium;
use App\OrganizationRoleUser;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Request;
use Yajra\DataTables\DataTables;

class UsersController extends Controller
{
    public function index()
    {
        abort_unless(\Gate::allows('user_access'), 403);
        
        if (auth()->user()->role()->id == 1)
        {
            $organizations = Organization::all(); 
            $roles = Role::all();
            $groups = Group::orderBy('organization_id', 'desc')->get() ;
        } 
        else 
        {
            $organizations = auth()->user()->organizations()->get(); 
            $roles = Role::where('id', '>',  auth()->user()->role()->id)->get();
            $groups = (auth()->user()->role()->id == 4) ? Group::where('organization_id', auth()->user()->current_organization_id)->get() : auth()->user()->groups()->orderBy('organization_id', 'desc')->get();   
        }
        $status_definitions = StatusDefinition::all();
        
        return view('users.index')
          //>with(compact('users'))
          ->with(compact('organizations'))
          ->with(compact('status_definitions'))
          ->with(compact('groups'))
          ->with(compact('roles'));
    }
    
    public function list()
    {
        $users = (auth()->user()->role()->id == 1) ? User::with(['status'])->get() : Organization::where('id', auth()->user()->current_organization_id)->get()->first()->users()->with(['status'])->get();
        
        return DataTables::of($users)
            ->addColumn('status', function ($users) {
                return $users->status->lang_de;                
            })
            ->addColumn('action', function ($users) {
                 $actions  = '';
                    if (\Gate::allows('user_show')){
                        $actions .= '<a href="'.route('users.show', $users->id).'" '
                                    . 'id="show-user-'.$users->id.'" '
                                    . 'class="btn">'
                                    . '<i class="fa fa-list-alt"></i>'
                                    . '</a>';
                    }
                    if (\Gate::allows('user_edit')){
                        $actions .= '<a href="'.route('users.edit', $users->id).'" '
                                    . 'id="edit-user-'.$users->id.'" '
                                    . 'class="btn">'
                                    . '<i class="fa fa-pencil-alt"></i>'
                                    . '</a>';
                    }
                    if (\Gate::allows('user_delete')){
                        $actions .= '<button type="button" '
                                . 'class="btn text-danger" '
                                . 'onclick="destroyDataTableEntry(\'users\','.$users->id.')">'
                                . '<i class="fa fa-trash"></i></button>';
                    }
              
                return $actions;
            })
           
            ->addColumn('check', '')
            ->setRowId('id')
            ->make(true);
    }
    

    public function create()
    {
        abort_unless(\Gate::allows('user_create'), 403);

        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        abort_unless(\Gate::allows('user_create'), 403);

        if (User::withTrashed()->where('email', request()->email)->exists())
        {
            User::withTrashed()->where('email', request()->email)->restore();
            $user = User::where('email', request()->email)->get()->first();
            $user->update($request->all());
        }
        else
        {
            $user = User::create($request->all());
        }
        
        
        /*
         * Todo User have to be enroled to (creators) institution 
         */
        OrganizationRoleUser::firstOrCreate(
            [
                'user_id'         => $user->id,
                'organization_id' => auth()->user()->current_organization_id,
            ],
            [
                'role_id'         => 6 //student
            ]
        );
        $user->current_organization_id = auth()->user()->current_organization_id; //set default org
        $user->save();
        
        //$user->roles()->sync($request->input('roles', []));
         return redirect($user->path());
    }

    public function edit(User $user)
    {
        abort_unless(\Gate::allows('user_edit'), 403);

        $roles = Role::all()->pluck('title', 'id');

        $user->load('roles');

        return view('users.edit', compact('roles', 'user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        abort_unless(\Gate::allows('user_edit'), 403);

        $user->update($request->all());
        //$user->roles()->sync($request->input('roles', []));

        return redirect()->route('users.index');
    }
    
    public function massUpdate(MassUpdateUserRequest $request)
    {
        //dd(request());
        if (isset(request()->password[0]))
        {
            User::whereIn('id', request('ids'))->update([
            'password' => Hash::make($request->password)
                ]); 
        }
        
        if (isset(request()->status_id[0]))
        {
        User::whereIn('id', request('ids'))->update([
            'status_id' => $request->status_id
                ]);  
        }
        
        return response(null, 204);
    }

    public function show(User $user)
    {
        abort_unless(\Gate::allows('user_show'), 403);
        $status_definitions = StatusDefinition::all();
        $user->load('roles');
        $user->load('organizations');

        return view('users.show')
                ->with(compact('user'))
                ->with(compact('status_definitions'));
    }

    public function destroy(User $user)
    {
        abort_unless(\Gate::allows('user_delete'), 403);

        $return = $user->delete();
        //todo concept to hard-delete users
        if (request()->wantsJson()){    
            return ['message' => $return];
        }
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        User::whereIn('id', request('ids'))->delete();
           
        return response(null, 204);
    }
    
    public function setCurrentOrganizationAndPeriod()
    {
        User::where('id', auth()->user()->id)->update([
            'current_period_id' => request('current_period_id'),
            'current_organization_id' => request('current_organization_id')
        ]); 
        
        return back();
    }
   
    /**
     * Set users Profile Image
     * @return type
     */
    public function setAvatar()
    {
        $medium = new Medium();
        dump(request());
        User::where('id', auth()->user()->id)->update([
            'medium_id' => (null !== $medium->getByFilemanagerPath(request('filepath'))) ? $medium->getByFilemanagerPath(request('filepath'))->id : null,
                ]); 
        
        return back();
    }
 
}
