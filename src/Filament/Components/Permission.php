<?php

namespace Obelaw\Permit\Filament\Components;

use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Obelaw\Permit\Attributes\PagePermission;
use Obelaw\Permit\Attributes\Permissions;
use Obelaw\Permit\Attributes\WidgetPermission;
use ReflectionClass;

class Permission extends Field
{
    public $component = null;
    public $permissions = [];
    public $pagePermissions = [];
    public $widgetPermissions = [];

    protected string $view = 'obelaw-permit::components.permissions';

    // Match parent signature: Field::make(?string $name = null): static
    public static function make(?string $name = null): static
    {
        $make = parent::make($name);
        // Default to resources component to preserve previous behavior
        $make->populateByComponent('resources');
        return $make;
    }

    public function component(string $component): static
    {
        $this->populateByComponent($component);
        return $this;
    }

    protected function populateByComponent(string $component): void
    {
        $panel = Filament::getCurrentOrDefaultPanel();

        if ($component === 'resources') {
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

            $this->component = $component;
            $this->permissions = $allPermissions;
            // reset others
            $this->pagePermissions = [];
            $this->widgetPermissions = [];
            return;
        }

        if ($component === 'pages') {
            $collection = collect([]);

            foreach ($panel->getPages() as $page) {
                $reflection = new ReflectionClass($page);
                $reflectionPermissions = $reflection->getAttributes(PagePermission::class);

                if (isset($reflectionPermissions[0])) {
                    $collection->push([
                        'id' => $reflectionPermissions[0]->getArguments()['id'],
                        'title' => $reflectionPermissions[0]->getArguments()['title'] ?? class_basename($page),
                        'description' => $reflectionPermissions[0]->getArguments()['description'] ?? null,
                        'category' => $reflectionPermissions[0]->getArguments()['category'] ?? 'global',
                    ]);
                }
            }

            $this->component = $component;
            $this->pagePermissions = $collection->groupBy('category')->toArray();
            // reset others
            $this->permissions = [];
            $this->widgetPermissions = [];
            return;
        }

        if ($component === 'widgets') {
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

            $this->component = $component;
            $this->widgetPermissions = $collection->groupBy('category')->toArray();
            // reset others
            $this->permissions = [];
            $this->pagePermissions = [];
            return;
        }

        // Unknown component: set only the flag and clear lists
        $this->component = $component;
        $this->permissions = [];
        $this->pagePermissions = [];
        $this->widgetPermissions = [];
    }

    public function getComponent()
    {
        return $this->evaluate($this->component);
    }

    public function getPermissions()
    {
        return $this->evaluate($this->permissions);
    }

    public function getPagePermissions()
    {
        return $this->evaluate($this->pagePermissions);
    }

    public function getWidgetPermissions()
    {
        return $this->evaluate($this->widgetPermissions);
    }
}
