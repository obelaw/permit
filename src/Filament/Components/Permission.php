<?php

namespace Obelaw\Permit\Filament\Components;

use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Obelaw\Permit\Attributes\Permissions;
use ReflectionClass;

class Permission extends Field
{
    public $permissions = [];

    protected string $view = 'obelaw-permit::components.permissions';

    public static function make(string $name): static
    {
        $make = parent::make($name);
        $panel = Filament::getCurrentPanel();

        $allPermissions = [];

        foreach ($panel->getResources() as $resource) {

            $allPermissions[class_basename($resource)] = [];

            $reflection = new ReflectionClass($resource);
            $reflectionPermissions = $reflection->getAttributes(Permissions::class);

            if (!empty($reflectionPermissions)) {
                foreach ($reflectionPermissions as $permission) {
                    $allPermissions[class_basename($resource)] = [
                        'id' => $permission->getArguments()['id'],
                        'title' => $permission->getArguments()['title'] ?? class_basename($resource),
                        'description' => $permission->getArguments()['description'] ?? null,
                        'permissions' => $permission->getArguments()['permissions'],
                    ];
                }
            }
        }

        $make->permissions = $allPermissions;

        return $make;
    }

    public function getPermissions()
    {
        return $this->evaluate($this->permissions);
    }
}
