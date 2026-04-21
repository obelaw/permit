<?php

namespace Obelaw\Permit\Filament\Pages;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Obelaw\Permit\Filament\Clusters\PermitCluster;
use Obelaw\Twist\Contracts\iSettings;
use Obelaw\Twist\Support\BaseSettingsPage;

class PermitSettings extends BaseSettingsPage implements iSettings
{
    protected static ?string $cluster = PermitCluster::class;
    protected static ?string $title = 'Permit Settings';
    protected ?string $heading = 'Permit Settings';
    protected ?string $subheading = 'Manage Permit package configuration';
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-8-tooth';

    public $curSettings = [];
    public $suspend_all_users;
    public $suspend_all_users_exceptions;
    public $auto_suspend_inactive;
    public $auto_suspend_inactive_duration_preset;
    public $auto_suspend_inactive_after_minutes;


    protected function getFormSchema(): array
    {
        return [
            Section::make('Access Control')
                ->description('Global access switches for all permit users.')
                ->schema([
                    Toggle::make('suspend_all_users')
                        ->label('Suspend all users')
                        ->helperText('Block all users from accessing the permit panel.'),

                    TagsInput::make('suspend_all_users_exceptions')
                        ->label('Exception Emails')
                        ->helperText('Add emails as tags. These users can access even when suspend all users is enabled.')
                        ->placeholder('user@example.com')
                        ->splitKeys(['Tab', 'Enter', ',', ';']),
                ]),

            Section::make('Inactivity Auto-Suspend')
                ->description('Automatically suspend users who have been inactive for too long.')
                ->schema([
                    Toggle::make('auto_suspend_inactive')
                        ->label('Enable inactivity auto-suspend')
                        ->helperText('Users will be suspended automatically if they exceed the inactivity threshold.')
                        ->live(),

                    Select::make('auto_suspend_inactive_duration_preset')
                        ->label('Suspend after')
                        ->helperText('Choose a preset duration, or select Custom to define minutes.')
                        ->options([
                            '1440' => '1 day',
                            '2880' => '2 days',
                            '10080' => '7 days',
                            'custom' => 'Custom (minutes)',
                        ])
                        ->default('custom')
                        ->live()
                        ->visible(fn ($get) => $get('auto_suspend_inactive')),

                    TextInput::make('auto_suspend_inactive_after_minutes')
                        ->label('Suspend after (minutes)')
                        ->helperText('Used only when Custom (minutes) is selected above.')
                        ->numeric()
                        ->minValue(1)
                        ->default(60)
                        ->visible(fn ($get) => $get('auto_suspend_inactive') && $get('auto_suspend_inactive_duration_preset') === 'custom'),
                ]),
        ];
    }

    public function mount(): void
    {
        $exceptions = oconfig()->get('obelaw.permit.suspend_all_users_exceptions', []);

        if (is_string($exceptions)) {
            $exceptions = preg_split('/[\r\n,;]+/', $exceptions) ?: [];
        }

        if (!is_array($exceptions)) {
            $exceptions = [];
        }

        $exceptions = array_map(fn ($email) => strtolower(trim((string) $email)), $exceptions);
        $exceptions = array_filter($exceptions, fn ($email) => $email !== '');
        $exceptions = array_values(array_unique($exceptions));

        $autoSuspendMinutes = max(1, (int) oconfig()->get('obelaw.permit.auto_suspend_inactive_after_minutes', 60));
        $preset = match ($autoSuspendMinutes) {
            1440 => '1440',
            2880 => '2880',
            10080 => '10080',
            default => 'custom',
        };

        $this->curSettings = [
            'suspend_all_users' => oconfig()->get('obelaw.permit.suspend_all_users', false),
            'suspend_all_users_exceptions' => $exceptions,
            'auto_suspend_inactive' => oconfig()->get('obelaw.permit.auto_suspend_inactive', false),
            'auto_suspend_inactive_duration_preset' => $preset,
            'auto_suspend_inactive_after_minutes' => $autoSuspendMinutes,
        ];

        $this->form->fill($this->curSettings);
    }

    public function save($inputs)
    {
        $rawExceptions = $inputs['suspend_all_users_exceptions'] ?? [];

        if (is_string($rawExceptions)) {
            $rawExceptions = preg_split('/[\r\n,;]+/', $rawExceptions) ?: [];
        }

        if (!is_array($rawExceptions)) {
            $rawExceptions = [];
        }

        $emails = $rawExceptions;
        $emails = array_map(fn ($email) => strtolower(trim($email)), $emails);
        $emails = array_filter($emails, fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL));
        $emails = array_values(array_unique($emails));

        $preset = (string) ($inputs['auto_suspend_inactive_duration_preset'] ?? 'custom');
        $autoSuspendMinutes = $preset === 'custom'
            ? max(1, (int) ($inputs['auto_suspend_inactive_after_minutes'] ?? 60))
            : max(1, (int) $preset);

        oconfig()->set('obelaw.permit.suspend_all_users', (bool) ($inputs['suspend_all_users'] ?? false));
        oconfig()->set('obelaw.permit.suspend_all_users_exceptions', $emails);
        oconfig()->set('obelaw.permit.auto_suspend_inactive', (bool) ($inputs['auto_suspend_inactive'] ?? false));
        oconfig()->set('obelaw.permit.auto_suspend_inactive_after_minutes', $autoSuspendMinutes);

        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();
    }
}
