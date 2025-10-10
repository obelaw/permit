<?php

namespace Obelaw\Permit\Filament\Resources\PermitUserResource;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Obelaw\Permit\Events\UserSelectedEvent;
use Obelaw\Permit\Filament\Resources\PermitUserResource;
use Obelaw\Permit\Models\PermitRule;

class ListUser extends ListRecords
{
    protected static string $resource = PermitUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(config('obelaw.permit.user.can_create')),

            Action::make('selectUser')
                ->label('Select User')
                ->icon('heroicon-o-user-plus')
                ->form([
                    Select::make('rule_id')
                        ->label('Select rule')
                        ->options(function () {
                            return PermitRule::pluck('name', 'id');
                        })
                        ->searchable()
                        ->required()
                        ->placeholder('Choose a rule'),

                    Select::make('user_id')
                        ->label('Select User')
                        ->options(function () {
                            return \App\Models\User::select('id', 'name', 'email')
                                ->get()
                                ->mapWithKeys(function ($user) {
                                    return [$user->id => "{$user->name} ({$user->email})"];
                                });
                        })
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            return \App\Models\User::where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(function ($user) {
                                    return [$user->id => "{$user->name} ({$user->email})"];
                                });
                        })
                        ->getOptionLabelUsing(function ($value) {
                            $user = \App\Models\User::find($value);
                            return $user ? "{$user->name} ({$user->email})" : '';
                        })
                        ->required()
                        ->placeholder('Choose a user'),
                ])
                ->action(function (array $data) {
                    // Handle the selected user
                    $selectedUser = \App\Models\User::find($data['user_id']);

                    $selectedUser->permit()->create([
                        'created_by' => Filament::auth()->user()->id,
                        'rule_id' => $data['rule_id'],
                        'is_active' => true,
                    ]);

                    event(new UserSelectedEvent(
                        $selectedUser,
                    ));

                    // Example notification
                    \Filament\Notifications\Notification::make()
                        ->title('User Selected')
                        ->body('User has been selected successfully.')
                        ->success()
                        ->send();
                })
                ->visible(config('obelaw.permit.user.can_select')),
        ];
    }

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $this->applyColumnSearchesToTableQuery($query);

        if (!Filament::auth()->user()->authable->rule->has_all_permissions)
            $query->where('created_by', auth()->user()->id);

        return $query;
    }
}
