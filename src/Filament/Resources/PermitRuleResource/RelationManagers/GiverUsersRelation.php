<?php

namespace Obelaw\Permit\Filament\Resources\PermitRuleResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Obelaw\Permit\Models\PermitUser;

class GiverUsersRelation extends RelationManager
{
    protected static ?string $title = 'Giver Users';
    protected static ?string $icon = 'heroicon-o-archive-box';
    protected static string $relationship = 'giverRules';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->required()
                    ->options(PermitUser::pluck('name', 'id'))
                    ->searchable()
                    ->columnSpan(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('user.rule.name')
                    ->label('Rule')
                    ->searchable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                DeleteAction::make(),
            ]);
    }
}
