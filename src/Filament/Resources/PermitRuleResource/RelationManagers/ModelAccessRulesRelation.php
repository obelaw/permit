<?php

namespace Obelaw\Permit\Filament\Resources\PermitRuleResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Obelaw\Permit\AccessModel;

class ModelAccessRulesRelation extends RelationManager
{
    protected static string $relationship = 'modelAccessRules';
    protected static ?string $title       = 'Record-Level Access Rules';
    protected static string|\BackedEnum|null $icon = 'heroicon-o-shield-check';

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Select::make('model_path')
                ->label('Model')
                ->options(fn() => AccessModel::getAccessModels())
                ->searchable()
                ->required()
                ->live()
                ->columnSpan(2),

            Select::make('field')
                ->label('Field')
                ->options(function (Get $get): array {
                    $modelClass = $get('model_path');


                    if (! $modelClass || ! class_exists($modelClass)) {
                        return [];
                    }

                    if (method_exists($modelClass, 'getPermitAccessFields')) {
                        return $modelClass::getPermitAccessFields();
                    }

                    // Fallback: derive fillable fields from a blank model instance
                    try {
                        $instance = new $modelClass;

                        return collect($instance->getFillable())
                            ->mapWithKeys(fn(string $f) => [$f => str($f)->replace('_', ' ')->title()->toString()])
                            ->all();
                    } catch (\Throwable) {
                        return [];
                    }
                })
                ->searchable()
                ->required()
                ->columnSpan(1),

            Select::make('operator')
                ->label('Operator')
                ->options([
                    '='       => 'Equals  ( = )',
                    '!='      => 'Not Equals  ( != )',
                    'in'      => 'In list  ( in )',
                    'not_in'  => 'Not in list  ( not_in )',
                    '>'       => 'Greater than  ( > )',
                    '<'       => 'Less than  ( < )',
                    'like'    => 'Contains  ( like )',
                ])
                ->required()
                ->default('=')
                ->columnSpan(1),

            TextInput::make('value')
                ->label('Value')
                ->required()
                ->helperText('Comma-separated for "in" / "not_in". Use * to bypass all filtering.')
                ->columnSpan(1),

            Select::make('boolean')
                ->label('Chain (AND / OR)')
                ->options(['and' => 'AND', 'or' => 'OR'])
                ->default('and')
                ->required()
                ->columnSpan(1),

        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('model_path')
                    ->label('Model')
                    ->formatStateUsing(fn(string $state) => class_basename($state))
                    ->tooltip(fn(string $state) => $state)
                    ->searchable(),

                TextColumn::make('field')->searchable(),
                TextColumn::make('operator'),
                TextColumn::make('value'),
                TextColumn::make('boolean')->badge(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
