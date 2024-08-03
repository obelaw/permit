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
use Obelaw\Permit\Filament\Resources\AdminResource\CreateAdmin;
use Obelaw\Permit\Filament\Resources\AdminResource\EditAdmin;
use Obelaw\Permit\Filament\Resources\AdminResource\ListAdmin;
use Obelaw\Permit\Models\Admin;
use Obelaw\Permit\Models\Rule;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Permit';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()
                    ->schema([
                        Select::make('rule_id')
                            ->label('Rule')
                            ->required()
                            ->options(Rule::pluck('name', 'id'))
                            ->searchable()
                            ->columnSpan(2),

                        TextInput::make('name')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('email')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('password')
                            ->required(fn (Page $livewire) => ($livewire instanceof CreateAdmin))
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
            'index' => ListAdmin::route('/'),
            'create' => CreateAdmin::route('/create'),
            'edit' => EditAdmin::route('/{record}/edit'),
        ];
    }
}
