<?php

namespace Obelaw\Permit\Filament\Resources;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Obelaw\Permit\Attributes\Permissions;
use Obelaw\Permit\Filament\Components\Permission;
use Obelaw\Permit\Filament\Resources\RuleResource\CreateRule;
use Obelaw\Permit\Filament\Resources\RuleResource\EditRule;
use Obelaw\Permit\Filament\Resources\RuleResource\ListRule;
use Obelaw\Permit\Models\PermitRule;
use Obelaw\Permit\Traits\PremitCan;

#[Permissions(
    id: 'permit.rules.viewAny',
    title: 'Rules',
    description: 'This rules',
    permissions: [
        'permit.rules.create' => 'Can Create',
        'permit.rules.edit' => 'Can Edit',
        'permit.rules.delete' => 'Can Delete',
    ]
)]
class RuleResource extends Resource
{
    use PremitCan;

    protected static ?array $canAccess = [
        'can_viewAny' => 'permit.rules.viewAny',
        'can_create' => 'permit.rules.create',
        'can_edit' => 'permit.rules.edit',
        'can_delete' => 'permit.rules.delete',
    ];

    protected static ?string $model = PermitRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Rules';

    protected static ?string $navigationGroup = 'Permit';

    public static function form(Form $form): Form
    {
        $rule = $form->getRecord();

        return $form
            ->schema([

                Section::make()
                    ->schema([
                        TextInput::make('name')->required(),

                        Toggle::make('has_all_permissions')
                            ->live()
                            ->afterStateUpdated(
                                fn($state, Set $set) => $state ? $set('select_permissions', true) : $set('select_permissions', false)
                            ),

                        Permission::make('permissions')
                            ->label('List of Permissions')
                            ->hidden(function (Get $get) use ($rule): bool {
                                return isset($rule->has_all_permissions) && $rule->has_all_permissions || $get('select_permissions');
                            }),

                    ])->columns(1)

            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
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
            'index' => ListRule::route('/'),
            'create' => CreateRule::route('/create'),
            'edit' => EditRule::route('/{record}/edit'),
        ];
    }
}
