# Permission-Map

This Permission-Map show the initial role configuation for curriculum.
To get the permission-key combine **permission** with **action**, linked by an **_** eg.:

User should have permission to create an group: **group_create**

There are some exceptions with an action custom action postfix:
- group_enrolment
- organization_enrolment
- user_reset_password

Permission/Action  | access | create | show | edit | delete | -
-----------------  | ------ | ---- | ------ | ---- | ------ | -
achievement  | admin, schooladmin, teacher | admin, schooladmin, teacher, student | - | - | -  
certificate  | admin, creator, schooladmin, teacher | admin, creator | admin, creator, schooladmin, teacher | admin, creator | admin, creator
course | - | admin, schooladmin, teacher | admin, schooladmin, teacher | admin, schooladmin, teacher | admin, schooladmin, teacher
curriculum | admin, creator| admin, creator | admin, creator, indexer, schooladmin, teacher, student, guest | admin, creator | admin, creator
grade | admin, creator  | admin, creator | admin, creator | admin, creator | admin, creator
group | admin, schooladmin, teacher | admin, schooladmin | admin, schooladmin, teacher | admin, schooladmin | admin, schooladmin
group_enrolment | - | - | - | - | - | admin, schooladmin
navigator | admin, creator | admin, creator | admin, creator, indexer, schooladmin, teacher, student, guest | admin, creator | admin, creator
objective | - | admin, creator | - | admin, creator | admin, creator
organization | admin | admin | admin, schooladmin, teacher | admin | admin
organization_enrolment | - | admin, schooladmin | admin, schooladmin | admin, schooladmin | -
organization_type  | admin, creator | admin, creator | admin, creator | admin, creator | admin, creator
period | admin, creator  | admin, creator | admin, creator | admin, creator | admin, creator | admin, creator
permission | admin | admin | admin | admin | admin
role | admin  | admin | admin | admin | admin
user | admin, schooladmin, teacher | admin, schooladmin | admin, schooladmin, teacher | admin, schooladmin, teacher | admin, schooladmin
user_reset_password | - | - | - | - | - | admin, schooladmin

## Usage
### PHP
Permissions can be checked on blade using the Gate::allows method.
Example:
```
$boolean = Gate::allows('curriculum_edit');  //returns true if auth()->user has permission

abort_unless(\Gate::allows('curriculum_edit'), 403); //returns status code 403 if auth()->user hasn't permission
```


### Blade
Permissions can be checked on blade using the @can directive.

Example:
```
@can('curriculum_edit')
    Only users with permission 'curriculum_edit' can see this. 
@endcan
```

### Vue
Permissions can be checked on vue components using the v-can directive.

Example:
```
<div v-can="'curriculum_edit'"> Only users with permission 'curriculum_edit' can see this. </div>
```