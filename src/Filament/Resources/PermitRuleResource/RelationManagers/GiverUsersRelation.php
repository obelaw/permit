<?php

namespace Obelaw\Permit\Filament\Resources\PermitRuleResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Obelaw\Permit\Models\PermitUser;

class GiverUsersRelation extends RelationManager
{
    protected static ?string $title = 'Giver Users';
    protected static string | \BackedEnum | null $icon = 'heroicon-o-archive-box';
    protected static string $relationship = 'giverRules';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
