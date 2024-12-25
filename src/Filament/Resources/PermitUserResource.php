<?php

namespace Obelaw\Permit\Filament\Resources;

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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Obelaw\Permit\Attributes\Permissions;
use Obelaw\Permit\Filament\Clusters\PermitCluster;
use Obelaw\Permit\Filament\Resources\PermitUserResource\CreateUser;
use Obelaw\Permit\Filament\Resources\PermitUserResource\EditUser;
use Obelaw\Permit\Filament\Resources\PermitUserResource\ListUser;
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

    protected static ?string $model = PermitUser::class;
    protected static ?string $cluster = PermitCluster::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()
                    ->schema([
                        Select::make('rule_id')
                            ->label('Rule')
                            ->required()
                            ->options(PermitRule::pluck('name', 'id'))
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

                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('email')
                    ->searchable(),

                ToggleColumn::make('is_active')
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
