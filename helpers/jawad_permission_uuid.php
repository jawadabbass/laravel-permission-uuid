<?php

use Jawadabbass\LaravelPermissionUuid\Models\Role;
use App\Models\User;
use Jawadabbass\LaravelPermissionUuid\Models\Permission;
use Jawadabbass\LaravelPermissionUuid\Models\PermissionRole;
use Jawadabbass\LaravelPermissionUuid\Models\PermissionGroup;
use Illuminate\Support\Facades\Auth;

function hasPermission($permission)
{
    if (!isAllowed($permission)) {
        abort(403, 'Unauthorized action to ' . $permission);
    }
}
function isAllowed($permission)
{

    if (isSuperAdmin()) {
        return true;
    }

    $permission_titles_array = (array)session('permission_titles_array');
    if (in_array($permission, $permission_titles_array)) {
        return true;
    }

    return false;
}

function isSuperAdmin()
{
    if (Auth::user()->user_type == 'super_admin') {
        return true;
    }
    return false;
}

function generateRolesCheckBoxes($user)
{
    $userRoleIdsArray = [];
    if ($user->id != '') {
        $userRoleIdsArray = $user->getUserRoleIds();
    }


    $str = '<div class="row m-3">';
    $roles = Role::all();
    foreach ($roles as $role) {
        $checked = (in_array($role->id, (array)$userRoleIdsArray)) ? 'checked="checked"' : '';
        $str .= '<div class="col-md-4"><label>';
        $str .= '<input type="checkbox" value="' . $role->id . '" ' . $checked . ' name="role_ids[]">';
        $str .= '<span></span>&nbsp;&nbsp;&nbsp;' . $role->title . '</label></div>';
    }
    return $str . '</div>';
}

function generatePermissionsCheckBoxes($role)
{
    $rolePermissionIdsArray = $permissionIdsArray = [];
    if (Auth::user()->user_type == 'super_admin') {
        $permissionIdsArray = Permission::pluck('id')->toArray();
    } else {
        $user = User::find(Auth::id());
        $roleIdsArray = $user->getUserRoleIds();
        $permissionIdsArray = PermissionRole::whereIn('role_id', $roleIdsArray)->pluck('permission_id')->toArray();
    }

    if ($role->id != '') {
        $rolePermissionIdsArray = $role->getRolePermissionIds();
    }

    $permissionGroupIdsArray = Permission::whereIn('id', $permissionIdsArray)->pluck('permission_group_id')->toArray();

    $str = '<div class="row"><div class="col-md-12">&nbsp;</div>';
    $permissionGroups = PermissionGroup::whereIn('id', $permissionGroupIdsArray)->get();
    foreach ($permissionGroups as $permissionGroup) {
        $str .= '<div class="col-md-12"><strong>' . $permissionGroup->title . '</strong></div>';

        $permissions = $permissionGroup->permissions;
        foreach ($permissions as $permission) {
            if (isAllowed($permission->title)) {
                $checked = (in_array($permission->id, (array)$rolePermissionIdsArray)) ? 'checked="checked"' : '';
                $str .= '<div class="col-md-4 checkbox-padding"><label class="checkbox checkbox-lg">';
                $str .= '<input type="checkbox" value="' . $permission->id . '" ' . $checked . ' name="permission_ids[]">';
                $str .= '<span></span>&nbsp;&nbsp;&nbsp;' . $permission->title . '</label></div>';
            }
        }
        $str .= '<div class="col-md-12">&nbsp;</div>';
    }
    return $str . '</div>';
}

function generatePermissionGroupsDropDown($defaultSelected = '', $createEmptyRow = true)
{
    $str = ($createEmptyRow) ? '<option value=""></option>' : '';
    $permissionGroups = PermissionGroup::all();
    foreach ($permissionGroups as $permissionGroup) {
        $selected = ($permissionGroup->id == $defaultSelected) ? 'selected="selected"' : '';
        $str .= '<option value="' . $permissionGroup->id . '" ' . $selected . '>' . $permissionGroup->title . '</option>';
    }
    return $str;
}
