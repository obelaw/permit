<?php

namespace Obelaw\Permit\Filament\Resources;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Obelaw\Permit\Filament\Components\Permission;
use Obelaw\Permit\Filament\Resources\RuleResource\CreateRule;
use Obelaw\Permit\Filament\Resources\RuleResource\EditRule;
use Obelaw\Permit\Filament\Resources\RuleResource\ListRule;
use Obelaw\Permit\Models\Rule;

class RuleResource extends Resource
{
    protected static ?string $model = Rule::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Permit';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()
                    ->schema([
                        TextInput::make('name')->required(),
                        Permission::make('permissions')
                            ->label('List of Permissions')
                            ->required()
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
