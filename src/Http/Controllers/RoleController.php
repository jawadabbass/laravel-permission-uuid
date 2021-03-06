<?php

namespace Jawadabbass\LaravelPermissionUuid\Http\Controllers;

use Jawadabbass\LaravelPermissionUuid\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Jawadabbass\LaravelPermissionUuid\Models\PermissionRole;
use Illuminate\Support\Facades\Auth;
use Jawadabbass\LaravelPermissionUuid\Http\Requests\RoleFormRequest;
use Jawadabbass\LaravelPermissionUuid\Models\RoleUser as ModelsRoleUser;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Jawadabbass\LaravelPermissionUuid\Models\RoleUser;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        hasPermission('View Roles');

        return view('jawad_permission_uuid::role.index');
    }

    public function fetchRolesAjax(Request $request)
    {
        hasPermission('View Roles');

        $roles = Role::select('*')->withoutGlobalScopes();
        return Datatables::of($roles)
            ->filter(function ($query) use ($request) {
                if ($request->has('title') && !empty($request->title)) {
                    $query->where('roles.title', 'like', "%{$request->get('title')}%");
                }
            })
            ->addColumn('title', function ($roles) {
                return Str::limit($roles->title, 100, '...');
            })
            ->addColumn('action', function ($roles) {
                $editStr = $deleteStr = '';
                if(isAllowed('Edit Role')){
                    $editStr = '<a href="' . route(config('jawad_permission_uuid.route_name_prefix').'roles.edit', [$roles->id]) . '" class="btn btn-warning m-1" title="Edit details">
                     Edit
                </a>';
                }
                if(isAllowed('Delete Role')){
                    $deleteStr = '<a href="javascript:void(0);" onclick="deleteRole(\'' . $roles->id . '\');" class="btn btn-danger m-1" title="Delete">
                     Delete
                </a>';
                }
                return $editStr.$deleteStr;
            })
            ->rawColumns(['action', 'title'])
            ->orderColumns(['role', 'status'], ':column $1')
            ->setRowId(function ($roles) {
                return 'rolesDtRow' . $roles->id;
            })
            ->make(true);
        //$query = $dataTable->getQuery()->get();
        //return $query;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        hasPermission('Add New Role');

        $role = new Role();
        return view('jawad_permission_uuid::role.create')->with('role', $role);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleFormRequest $request)
    {
        hasPermission('Add New Role');

        $role = new Role();
        $role->title = $request->input('title');
        $role->created_by_company_id = Auth::user()->company_id;
        $role->created_by_user_id = Auth::id();
        $role->save();
        /*         * ************************************ */

        $this->setRolePermissions($request, $role);

        flash('Role has been added!', 'success');
        return Redirect::route(config('jawad_permission_uuid.route_name_prefix').'roles.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        hasPermission('Edit Role');

        return view('jawad_permission_uuid::role.edit')
            ->with('role', $role);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RoleFormRequest $request, Role $role)
    {
        hasPermission('Edit Role');

        $role->title = $request->input('title');
        $role->created_by_company_id = Auth::user()->company_id;
        $role->save();
        /*         * ************************************ */

        $this->setRolePermissions($request, $role);

        flash('Role has been updated!', 'success');
        return Redirect::route(config('jawad_permission_uuid.route_name_prefix').'roles.index');
    }

    private function setRolePermissions($request, $role){

        $permissionIds = $request->permission_ids;
        if(count($permissionIds) > 0){
            PermissionRole::where('role_id', 'like', $role->id)->delete();
            foreach($permissionIds as $permission_id){
                $rolePermission = new PermissionRole();
                $rolePermission->id = Str::uuid();
                $rolePermission->role_id = $role->id;
                $rolePermission->permission_id = $permission_id;
                $rolePermission->save();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        hasPermission('Delete Role');

        PermissionRole::where('role_id', 'like', $role->id)->delete();
        RoleUser::where('role_id', 'like', $role->id)->delete();
        $role->delete();
        echo 'ok';
    }

    public function makeActiveRole(Request $request)
    {
        hasPermission('Edit Role');

        $id = $request->input('id');
        try {
            $role = Role::withoutGlobalScopes()->findOrFail($id);
            $role->status = 'active';
            $role->update();
            echo 'ok';
        } catch (ModelNotFoundException $e) {
            echo 'notok';
        }
    }

    public function makeNotActiveRole(Request $request)
    {
        hasPermission('Edit Role');

        $id = $request->input('id');
        try {
            $role = Role::withoutGlobalScopes()->findOrFail($id);
            $role->status = 'inactive';
            $role->update();
            echo 'ok';
        } catch (ModelNotFoundException $e) {
            echo 'notok';
        }
    }

    public function sortRoles()
    {
        hasPermission('Sort Roles');
        return view('jawad_permission_uuid::role.sort');
    }

    public function rolesSortData(Request $request)
    {
        hasPermission('Sort Roles');

        $roles = Role::select('roles.id', 'roles.title', 'roles.sort_order')
        ->get();
        $str = '<ul id="sortable">';
        if ($roles != null) {
            foreach ($roles as $role) {
                $str .= '<li class="ui-state-default" id="' . $role->id . '"><i class="fa fa-sort"></i>' . $role->title . '</li>';
            }
        }
        echo $str . '</ul>';
    }

    public function rolesSortUpdate(Request $request)
    {
        hasPermission('Sort Roles');

        $rolesOrder = $request->input('rolesOrder');
        $rolesOrderArray = explode(',', $rolesOrder);
        $count = 1;
        foreach ($rolesOrderArray as $roleId) {
            $role = Role::withoutGlobalScopes()->find($roleId);
            $role->sort_order = $count;
            $role->update();
            $count++;
        }
    }
}
