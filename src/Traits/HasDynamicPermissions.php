<?php

namespace Obelaw\Permit\Traits;

use Illuminate\Database\Eloquent\Builder;
use Obelaw\Permit\Models\PermitModelAccessRule;

/**
 * HasDynamicPermissions
 *
 * Apply to any Eloquent model to enforce table-driven record-level access
 * control.  Rules are stored per-role in obelaw_permit_model_access_rules,
 * keyed by the fully-qualified model class name (model_path).
 *
 * Usage
 * -----
 * Add the trait to your model:
 *
 *   use Obelaw\Permit\Traits\HasDynamicPermissions;
 *
 *   class Warehouse extends Model
 *   {
 *       use HasDynamicPermissions;
 *
 *       // Expose filterable fields for the Filament UI
 *       public static function getPermitAccessFields(): array
 *       {
 *           return ['status' => 'Status', 'region' => 'Region'];
 *       }
 *   }
 *
 * Wildcard bypass
 * ---------------
 * A rule with value = "*" causes ALL filtering for that model to be skipped.
 *
 * Supported operators: =, !=, in, not_in, >, <, like
 * For "in" / "not_in" use comma-separated values: "us,eu,uk"
 */
trait HasDynamicPermissions
{
    /**
     * Per-request, per-rule cache: rule_id → Collection of PermitModelAccessRule rows.
     * Populated on first access, shared across all trait-using models in the same request.
     */
    private static array $_ruleRowsCache = [];

    /**
     * Boot the trait and register the global scope.
     */
    public static function bootHasDynamicPermissions(): void
    {
        static::addGlobalScope('dynamic_permissions', function (Builder $builder) {
            static::applyDynamicPermissions($builder);
        });
    }

    /**
     * Public entry point — can be called manually from getEloquentQuery() if needed.
     */
    public static function applyDynamicPermissions(Builder $builder): void
    {
        $modelPath = static::class;
        $rules     = static::resolveRulesForModel($modelPath);

        if ($rules === null) {
            return; // no user / no rules — pass through
        }

        if ($rules === '*') {
            return; // wildcard — bypass all filtering
        }

        /** @var array<int, PermitModelAccessRule> $rules */
        $builder->where(function (Builder $query) use ($rules) {
            foreach ($rules as $index => $rule) {
                $boolean = $index === 0 ? 'and' : $rule->boolean;
                static::applyRuleCondition($query, $rule->field, $rule->operator, $rule->value, $boolean);
            }
        });
    }

    /**
     * Resolve rules for the given model path.
     *
     * Returns:
     *   null   — no authenticated permit user or no rules for this model (pass through)
     *   '*'    — wildcard: bypass all filtering
     *   array  — PermitModelAccessRule objects to apply
     */
    private static function resolveRulesForModel(string $modelPath): null|string|array
    {
        $permitUser = auth()->user()?->permit()->first();

        if (! $permitUser || ! $permitUser->rule_id) {
            return null;
        }

        $ruleId = $permitUser->rule_id;

        // Load all rules for this role once per request and cache them
        if (! isset(static::$_ruleRowsCache[$ruleId])) {
            static::$_ruleRowsCache[$ruleId] = PermitModelAccessRule::where('rule_id', $ruleId)
                ->get()
                ->groupBy('model_path');
        }

        $grouped   = static::$_ruleRowsCache[$ruleId];
        $modelRows = $grouped->get($modelPath)?->all() ?? [];

        if (empty($modelRows)) {
            return null;
        }

        // Wildcard check: any row with value "*" bypasses filtering
        foreach ($modelRows as $row) {
            if ($row->value === '*') {
                return '*';
            }
        }

        return $modelRows;
    }

    /**
     * Check whether this loaded model instance is accessible to the current user.
     *
     * The global scope handles the listing (SQL-level).
     * Use this method to guard ViewRecord / infolist pages at the PHP level,
     * so the record can still be fetched with withoutGlobalScope() and then
     * validated — avoiding a misleading 404 vs a proper 403.
     *
     * Wire it up in your Filament Resource:
     *
     *   public static function canView(Model $record): bool
     *   {
     *       return $record->canAccessRecord();
     *   }
     *
     *   // Override resolveRecordRouteBinding so ViewRecord can load the record
     *   // even when the global scope would normally exclude it:
     *   public static function resolveRecordRouteBinding(int|string $key): ?Model
     *   {
     *       return static::getModel()::withoutGlobalScope('dynamic_permissions')
     *           ->find($key);
     *   }
     */
    public function canAccessRecord(): bool
    {
        $rules = static::resolveRulesForModel(static::class);

        // No rules configured or wildcard — always accessible
        if ($rules === null || $rules === '*') {
            return true;
        }

        $pass = true;

        foreach ($rules as $index => $rule) {
            $field   = $rule->field;
            $boolean = $index === 0 ? 'and' : $rule->boolean;

            // Only allow safe field names before calling getAttribute()
            if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field)) {
                continue;
            }

            $matches = static::evaluateRule(
                $this->getAttribute($field),
                $rule->operator,
                $rule->value
            );

            $pass = $boolean === 'or' ? ($pass || $matches) : ($pass && $matches);
        }

        return $pass;
    }

    /**
     * Evaluate a single rule against an attribute value in PHP (no DB hit).
     */
    private static function evaluateRule(mixed $actual, string $operator, string $value): bool
    {
        return match ($operator) {
            '='      => (string) $actual === $value,
            '!='     => (string) $actual !== $value,
            '>'      => $actual > $value,
            '<'      => $actual < $value,
            'like'   => str_contains((string) $actual, str_replace('%', '', $value)),
            'in'     => in_array((string) $actual, array_map('trim', explode(',', $value)), true),
            'not_in' => ! in_array((string) $actual, array_map('trim', explode(',', $value)), true),
            default  => false,
        };
    }

    /**
     * Apply a single rule condition using parameter binding (SQL injection safe).
     *
     * Column names are validated via strict regex before being used — they
     * are never interpolated as raw SQL strings.
     */
    protected static function applyRuleCondition(
        Builder $query,
        string  $field,
        string  $operator,
        string  $value,
        string  $boolean
    ): void {
        // Whitelist allowed operators
        if (! in_array($operator, ['=', '!=', 'in', 'not_in', '>', '<', 'like'], true)) {
            return;
        }

        // Strict field name validation — allows table.column qualified names
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $field)) {
            return;
        }

        $or = $boolean === 'or';

        switch ($operator) {
            case 'in':
                $values = array_map('trim', explode(',', $value));
                $or ? $query->orWhereIn($field, $values) : $query->whereIn($field, $values);
                break;

            case 'not_in':
                $values = array_map('trim', explode(',', $value));
                $or ? $query->orWhereNotIn($field, $values) : $query->whereNotIn($field, $values);
                break;

            default:
                // All scalar values go through PDO parameter binding via Eloquent
                $or ? $query->orWhere($field, $operator, $value) : $query->where($field, $operator, $value);
                break;
        }
    }
}
