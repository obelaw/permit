<?php

namespace Obelaw\Permit\Filament\Components;

use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Obelaw\Permit\Attributes\Permissions;
use Obelaw\Permit\Attributes\WidgetPermission;
use ReflectionClass;

class Permission extends Field
{
    public $component = null;
    public $permissions = [];
    public $widgetPermissions = [];

    protected string $view = 'obelaw-permit::components.permissions';

    public static function make(string $name, $component = 'resources'): static
    {
        $make = parent::make($name);
        $panel = Filament::getCurrentPanel();

        $allPermissions = [];

        if ($component == 'resources') {
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

            $make->component = $component;
            $make->permissions = $allPermissions;
        }

        if ($component == 'widgets') {
            $collection = collect([]);

            foreach ($panel->getWidgets() as $widget) {
                $reflection = new ReflectionClass($widget);
                $reflectionPermissions = $reflection->getAttributes(WidgetPermission::class);

                if (isset($reflectionPermissions[0])) {
                    $collection->push([
                        'id' => $reflectionPermissions[0]->getArguments()['id'],
                        'title' => $reflectionPermissions[0]->getArguments()['title'] ?? class_basename($widget),
                        'description' => $reflectionPermissions[0]->getArguments()['description'] ?? null,
                        'category' => $reflectionPermissions[0]->getArguments()['category'] ?? 'global',
                    ]);
                }
            }

            $make->component = $component;
            
            $make->widgetPermissions = $collection->groupBy('category')->toArray();
        }


        return $make;
    }

    public function getComponent()
    {
        return $this->evaluate($this->component);
    }

    public function getPermissions()
    {
        return $this->evaluate($this->permissions);
    }

    public function getWidgetPermissions()
    {
        return $this->evaluate($this->widgetPermissions);
    }
}
