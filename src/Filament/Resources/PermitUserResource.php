<?php

namespace Obelaw\Permit\Filament\Resources;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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
use Twist\Tenancy\Concerns\HasDBTenancy;

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
                            ->required()
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
                            ->columnSpan(2),
                    ])->columns(2)

            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rule.name')
                    ->searchable(),

                TextColumn::make('authable.name')
                    ->searchable(),

                TextColumn::make('authable.email')
                    ->searchable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
            ])
            ->filters([
                //add filter by user rule
                SelectFilter::make('rule_id')
                    ->label('Rule')
                    ->relationship('rule', 'name')
                    ->searchable()
                    ->preload(),

                // is_active
                SelectFilter::make('is_active')
                    ->label('Active Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->visible(config('obelaw.permit.user.can_create')),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->color('success'),

                    BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn($records) => $records->each->update(['is_active' => false]))
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
}
