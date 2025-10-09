<?php

namespace Obelaw\Permit\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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

    protected static ?array $canAccess = [
        'can_viewAny' => 'permit.admins.viewAny',
        'can_create' => 'permit.admins.create',
        'can_edit' => 'permit.admins.edit',
        'can_delete' => 'permit.admins.delete',
    ];
    protected static ?string $cluster = PermitCluster::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Users';

    public static function getModel(): string
    {
        $defaultGuard = config('obelaw.permit.guard');
        $guard = config("auth.guards.$defaultGuard.provider");
        return config("auth.providers.$guard.model");
    }

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

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
            ->modifyQueryUsing(fn($query) => $query->whereHas('authable', function ($q) {
                if (!Filament::auth()->user()->authable->rule->has_all_permissions) {
                    $q->whereIn('rule_id', PermitGiverRule::where('user_id', auth()->user()->id)->pluck('rule_id'));
                }
            })->with('authable.rule')->orderBy('created_at', 'desc'))
            ->columns([
                TextColumn::make('authable.rule.name')
                    ->searchable(),

                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('email')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
