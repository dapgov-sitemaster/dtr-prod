<?php

namespace App\Permissions;

use App\Models\Permission;

trait HasPermissionTrait
{
    public function givePermissionsTo(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        if ($permissions->isEmpty()) {
            return $this;
        }

        $this->permissions()->syncWithoutDetaching($permissions->modelKeys());

        return $this;
    }

    public function withdrawPermissionsTo(...$permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);

        return $this;
    }

    public function refreshPermissions(...$permissions)
    {
        $permissionIds = $this->getAllPermissions($permissions)->modelKeys();
        $this->permissions()->sync($permissionIds);

        return $this;
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions');
    }

    public function hasPermissionTo($permission)
    {
        return (bool) $this->permissions()->where('slug', $permission->slug)->count();
    }

    protected function getAllPermissions(array $permissions)
    {
        return Permission::whereIn('slug', $permissions)->get();
    }
}
