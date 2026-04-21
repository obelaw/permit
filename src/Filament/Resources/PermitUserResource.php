<?php

namespace Obelaw\Permit\Filament\Resources;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Obelaw\Permit\Attributes\Permissions;
use Obelaw\Permit\Facades\Permit;
use Obelaw\Permit\Filament\Clusters\PermitCluster;
use Obelaw\Permit\Filament\Resources\PermitUserResource\CreateUser;
use Obelaw\Permit\Filament\Resources\PermitUserResource\EditUser;
use Obelaw\Permit\Filament\Resources\PermitUserResource\ListUser;
use Obelaw\Permit\Models\PermitGiverRule;
use Obelaw\Permit\Models\PermitRule;
use Obelaw\Permit\Models\PermitUser;
use Obelaw\Permit\Traits\PremitCan;
use Obelaw\Twist\Tenancy\Concerns\HasDBTenancy;

#[Permissions(
    id: 'permit.admins.viewAny',
    title: 'Admins',
    description: 'This admins',
    permissions: [
        'permit.admins.create' => 'Can Create',
        'permit.admins.edit' => 'Can Edit',
        'permit.admins.delete' => 'Can Delete',
    ]
)]
class PermitUserResource extends Resource
{
    use PremitCan;
    use HasDBTenancy;

    protected static ?array $canAccess = [
        'can_viewAny' => 'permit.admins.viewAny',
        'can_create' => 'permit.admins.create',
        'can_edit' => 'permit.admins.edit',
        'can_delete' => 'permit.admins.delete',
    ];
    protected static ?string $model = PermitUser::class;
    protected static ?string $cluster = PermitCluster::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Users';

    // public static function getModel(): string
    // {
    //     $defaultGuard = config('obelaw.permit.guard');
    //     $guard = config("auth.guards.$defaultGuard.provider");
    //     return config("auth.providers.$guard.model");
    // }

    public static function canViewAny(): bool
    {
        if (auth()->user()->can_create) {
            return true;
        }

        return Permit::can(static::$canAccess['can_viewAny']);
    }

    public static function canCreate(): bool
    {
        if (auth()->user()->can_create) {
            return true;
        }

        return Permit::can(static::$canAccess['can_create']);
    }

    public static function canEdit(Model $record): bool
    {
        if (auth()->user()->can_create) {
            return true;
        }

        return Permit::can(static::$canAccess['can_edit']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make()
                    ->schema([
                        Select::make('rule_id')
                            ->label('Rule')
                            ->required()
                            ->options(function () {
                                if (!Filament::auth()->user()->authable->rule->has_all_permissions)
                                    return PermitGiverRule::where('user_id', auth()->user()->id)
                                        ->get()
                                        ->pluck('rule.name', 'rule.id');

                                return PermitRule::pluck('name', 'id');
                            })
                            ->searchable()
                            ->columnSpan(2),

                        TextInput::make('name')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(
                                table: User::class,
                                column: 'email',
                                ignorable: fn(?PermitUser $record): ?Model => $record?->authable,
                            )
                            ->columnSpan(1),

                        TextInput::make('password')
                            ->required(fn(Page $livewire) => ($livewire instanceof CreateAdmin))
                            ->password()
                            ->revealable(),

                        Toggle::make('can_create')
                            ->label('Can Create Accounts')
                            ->helperText('Allow this user to create new accounts')
                            ->disabled(fn() => !Filament::auth()->user()->authable->rule->has_all_permissions)
                            ->columnSpan(span: 2),

                        Toggle::make('is_active')
                            ->disabled(fn(?PermitUser $record): bool => static::shouldPreventSelfDeactivation() && static::isCurrentUserRecord($record))
                            ->columnSpan(2),
                    ])->columns(2)

            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rule.name')
                    ->label('Rule'),

                TextColumn::make('authable.name')
                    ->label('Name'),

                TextColumn::make('authable.email')
                    ->label('Email'),

                ToggleColumn::make('is_active')
                    ->disabled(fn(?PermitUser $record): bool => static::shouldPreventSelfDeactivation() && static::isCurrentUserRecord($record))
                    ->label('Active'),

                ToggleColumn::make('is_suspend')
                    ->disabled(fn(?PermitUser $record): bool => static::shouldPreventSelfDeactivation() && static::isCurrentUserRecord($record))
                    ->getStateUsing(fn(?PermitUser $record): bool => $record?->is_suspend !== null)
                    ->updateStateUsing(fn(?PermitUser $record, bool $state) => $record?->update([
                        'is_suspend' => $state ? now() : null,
                    ]))
                    ->label('Suspended'),
            ])
            ->filters([
                //add filter by user rule
                SelectFilter::make('rule_id')
                    ->label('Rule')
                    ->relationship('rule', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('email')
                    ->label('Email')
                    ->form([
                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('Search by email'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $email = trim((string) ($data['email'] ?? ''));

                        if ($email === '') {
                            return $query;
                        }

                        return $query->whereHas('authable', fn (Builder $authableQuery): Builder =>
                            $authableQuery->where('email', 'like', "%{$email}%")
                        );
                    }),

                // is_active
                SelectFilter::make('is_active')
                    ->label('Active Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),

                SelectFilter::make('is_suspend')
                    ->label('Suspended Status')
                    ->options([
                        '1' => 'Suspended',
                        '0' => 'Not Suspended',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ((string) ($data['value'] ?? '')) {
                            '1' => $query->whereNotNull('is_suspend'),
                            '0' => $query->whereNull('is_suspend'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->visible(config('obelaw.permit.user.can_create')),
                Action::make('revokeAppAuthentication')
                    ->label('Revoke App Code')
                    ->icon('heroicon-o-key')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (PermitUser $record): void {
                        $authable = $record->authable;

                        if (! ($authable instanceof HasAppAuthentication)) {
                            return;
                        }

                        $appAuthentication = app(AppAuthentication::class);
                        $appAuthentication->saveSecret($authable, null);

                        if ($authable instanceof HasAppAuthenticationRecovery) {
                            $appAuthentication->saveRecoveryCodes($authable, null);
                        }

                        Notification::make()
                            ->title('App authentication code revoked successfully.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (?PermitUser $record): bool =>
                        $record?->authable instanceof HasAppAuthentication
                        && filled($record->authable->getAppAuthenticationSecret())
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn($records) => $records
                            ->reject(fn(PermitUser $record) => static::shouldPreventSelfDeactivation() && static::isCurrentUserRecord($record))
                            ->each
                            ->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->color('success'),

                    BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn($records) => $records
                            ->reject(fn(PermitUser $record) => static::shouldPreventSelfDeactivation() && static::isCurrentUserRecord($record))
                            ->each
                            ->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->color('danger'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUser::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    protected static function isCurrentUserRecord(?PermitUser $record): bool
    {
        return $record?->authable?->is(auth()->user()) ?? false;
    }

    protected static function shouldPreventSelfDeactivation(): bool
    {
        return (bool) config('obelaw.permit.user.prevent_self_deactivation', true);
    }

    protected static function shouldPreventSelfDelete(): bool
    {
        return (bool) config('obelaw.permit.user.prevent_self_delete', true);
    }
}
