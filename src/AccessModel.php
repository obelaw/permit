<?php

namespace Obelaw\Permit;

class AccessModel
{
    /** @var array<class-string, string> */
    private static array $accessModels = [];

    /**
     * Register an Eloquent model for record-level access control.
     * Call this from your AppServiceProvider::boot().
     *
     * @param  class-string  $modelClass
     * @param  string|null   $label  Defaults to class_basename($modelClass)
     */
    public static function registerAccessModel(string $modelClass, ?string $label = null): void
    {
        static::$accessModels[$modelClass] = $label ?? class_basename($modelClass);
    }

    /**
     * Return all registered models as [FQCN => label] for Filament Select options.
     *
     * @return array<class-string, string>
     */
    public static function getAccessModels(): array
    {
        return static::$accessModels;
    }
}
