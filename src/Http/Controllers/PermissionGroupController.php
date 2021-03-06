<?php

namespace Jawadabbass\LaravelPermissionUuid\Http\Controllers;

use Jawadabbass\LaravelPermissionUuid\Models\PermissionGroup;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;
use Jawadabbass\LaravelPermissionUuid\Http\Requests\PermissionGroupFormRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PermissionGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        hasPermission('View Permission Groups');

        return view('jawad_permission_uuid::permissionGroup.index');
    }

    public function fetchPermissionGroupsAjax(Request $request)
    {
        hasPermission('View Permission Groups');

        $permissionGroups = PermissionGroup::select('*')->withoutGlobalScopes();
        return Datatables::of($permissionGroups)
            ->filter(function ($query) use ($request) {
                if ($request->has('title') && !empty($request->title)) {
                    $query->where('permissions_group.title', 'like', "%{$request->get('title')}%");
                }
            })
            ->addColumn('title', function ($permissionGroups) {
                return Str::limit($permissionGroups->title, 100, '...');
            })
            ->addColumn('action', function ($permissionGroups) {
                $editStr = $deleteStr = '';
                if(isAllowed('Edit Role')){
                    $editStr = '<a href="' . route(config('jawad_permission_uuid.route_name_prefix').'permissionGroup.edit', [$permissionGroups->id]) . '" class="btn btn-warning m-1" title="Edit details">
                     Edit
                </a>';
                }
                if(isAllowed('Delete Role')){
                    $deleteStr = '<a href="javascript:void(0);" onclick="deletePermissionGroup(\'' . $permissionGroups->id . '\');" class="btn btn-danger m-1" title="Delete">
                     Delete
                </a>';
                }
                return $editStr.$deleteStr;
            })
            ->rawColumns(['action', 'title'])
            ->orderColumns(['title', 'status'], ':column $1')
            ->setRowId(function ($permissionGroups) {
                return 'permissionGroupDtRow' . $permissionGroups->id;
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
        hasPermission('Add New Permission Group');

        $permissionGroup = new PermissionGroup();
        return view('jawad_permission_uuid::permissionGroup.create')->with('permissionGroup', $permissionGroup);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PermissionGroupFormRequest $request)
    {
        hasPermission('Add New Permission Group');

        $permissionGroup = new PermissionGroup();
        $permissionGroup->title = $request->input('title');
        $permissionGroup->save();
        /*         * ************************************ */
        flash('Permission Group has been added!', 'success');
        return Redirect::route(config('jawad_permission_uuid.route_name_prefix').'permissionGroup.index');
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
    public function edit(PermissionGroup $permissionGroup)
    {
        hasPermission('Edit Permission Group');

        return view('jawad_permission_uuid::permissionGroup.edit')
            ->with('permissionGroup', $permissionGroup);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PermissionGroupFormRequest $request, PermissionGroup $permissionGroup)
    {
        hasPermission('Edit Permission Group');

        $permissionGroup->title = $request->input('title');
        $permissionGroup->save();
        /*         * ************************************ */
        flash('Permission Group has been updated!', 'success');
        return Redirect::route(config('jawad_permission_uuid.route_name_prefix').'permissionGroup.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PermissionGroup $permissionGroup)
    {
        hasPermission('Delete Permission Group');
        $permissionGroup->delete();
        echo 'ok';
    }

    public function sortPermissionGroups()
    {
        hasPermission('Sort Permission Groups');

        return view('jawad_permission_uuid::permissionGroup.sort');
    }

    public function permissionGroupSortData(Request $request)
    {
        hasPermission('Sort Permission Groups');

        $permissionGroups = PermissionGroup::select('permissions_group.id', 'permissions_group.title', 'permissions_group.sort_order')
        ->get();
        $str = '<ul id="sortable">';
        if ($permissionGroups != null) {
            foreach ($permissionGroups as $permissionGroup) {
                $str .= '<li class="ui-state-default" id="' . $permissionGroup->id . '"><i class="fa fa-sort"></i>' . $permissionGroup->title . '</li>';
            }
        }
        echo $str . '</ul>';
    }

    public function permissionGroupSortUpdate(Request $request)
    {
        hasPermission('Sort Permission Groups');

        $permissionGroupOrder = $request->input('permissionGroupOrder');
        $permissionGroupOrderArray = explode(',', $permissionGroupOrder);
        $count = 1;
        foreach ($permissionGroupOrderArray as $permissionGroupId) {
            $permissionGroup = PermissionGroup::withoutGlobalScopes()->find($permissionGroupId);
            $permissionGroup->sort_order = $count;
            $permissionGroup->update();
            $count++;
        }
    }

}
