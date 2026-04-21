<?php

namespace Obelaw\Permit\Filament\Resources\PermitUserResource;

use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Obelaw\Permit\Filament\Resources\PermitUserResource;
use Obelaw\Permit\Models\PermitUser;
use Obelaw\Trail\Models\Trail;
use Livewire\Attributes\Url;

class UserTrails extends Page implements Tables\Contracts\HasTable
{
    use InteractsWithRecord;
    use Tables\Concerns\InteractsWithTable {
        makeTable as makeBaseTable;
    }

    protected static string $resource = PermitUserResource::class;

    #[Url(as: 'filters')]
    public ?array $tableFilters = null;

    #[Url(as: 'search')]
    public $tableSearch = '';

    #[Url(as: 'sort')]
    public ?string $tableSort = null;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(static::canAccess(['record' => $this->getRecord()]), 403);
    }

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (! $record instanceof PermitUser) {
            return false;
        }

        if (! PermitUserResource::canView($record)) {
            return false;
        }

        return PermitUserResource::recordHasTrail($record);
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }

    public function getTitle(): string | Htmlable
    {
        return 'User Trails';
    }

    public function getBreadcrumb(): string
    {
        return 'Trails';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => $this->getTableQuery())
            ->heading('User Trails')
            ->columns([
                TextColumn::make('trailable')
                    ->label('Trailable')
                    ->state(fn(Trail $record): string => class_basename((string) $record->trailable_type) . '#' . (string) $record->trailable_id)
                    ->toggleable(),
                TextColumn::make('event')
                    ->badge()
                    ->searchable(),
                TextColumn::make('method')
                    ->label('Method')
                    ->badge()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('url')
                    ->label('URL')
                    ->limit(50)
                    ->tooltip(fn(Trail $record): ?string => $record->url)
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->actions([
                Action::make('viewChanges')
                    ->label('Changes')
                    ->icon('heroicon-o-code-bracket-square')
                    ->modalHeading('Diff')
                    ->modalContent(fn(Trail $record): HtmlString => static::renderDiff($record))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $record = $this->getRecord();

        if (! $record instanceof PermitUser) {
            return Trail::query()->whereRaw('1 = 0');
        }

        $authable = $record->authable;

        if (! PermitUserResource::recordHasTrail($record)) {
            return Trail::query()->whereRaw('1 = 0');
        }

        return $authable->trails()->getQuery();
    }

    private static function renderDiff(Trail $record): HtmlString
    {
        $old = $record->snapshot ?? [];
        $new = $record->changes ?? [];

        $allKeys = array_unique(array_merge(array_keys((array) $old), array_keys((array) $new)));

        $rows = '';
        foreach ($allKeys as $key) {
            $oldVal = array_key_exists($key, (array) $old) ? $old[$key] : null;
            $newVal = array_key_exists($key, (array) $new) ? $new[$key] : null;

            $oldStr = is_array($oldVal) ? json_encode($oldVal, JSON_UNESCAPED_UNICODE) : (string) ($oldVal ?? '');
            $newStr = is_array($newVal) ? json_encode($newVal, JSON_UNESCAPED_UNICODE) : (string) ($newVal ?? '');

            if ($oldStr === $newStr) {
                continue;
            }

            if ($old !== []) {
                $rows .= '<div style="display:flex;background:#ffeef0;padding:3px 12px;">';
                $rows .= '<span style="color:#b31d28;width:16px;flex-shrink:0;">-</span>';
                $rows .= '<span style="color:#57606a;margin-right:8px;min-width:140px;flex-shrink:0;">' . htmlspecialchars($key) . '</span>';
                $rows .= '<span style="color:#b31d28;word-break:break-all;">' . htmlspecialchars($oldStr) . '</span>';
                $rows .= '</div>';
            }

            $rows .= '<div style="display:flex;background:#e6ffed;padding:3px 12px;">';
            $rows .= '<span style="color:#1a7f37;width:16px;flex-shrink:0;">+</span>';
            $rows .= '<span style="color:#57606a;margin-right:8px;min-width:140px;flex-shrink:0;">' . htmlspecialchars($key) . '</span>';
            $rows .= '<span style="color:#1a7f37;word-break:break-all;">' . htmlspecialchars($newStr) . '</span>';
            $rows .= '</div>';
        }

        if ($rows === '') {
            $rows = '<div style="padding:12px;color:#57606a;">No changes recorded.</div>';
        }

        $html = '<div style="font-family:ui-monospace,SFMono-Regular,monospace;font-size:12px;border:1px solid #d0d7de;border-radius:6px;overflow:hidden;">';
        $html .= '<div style="background:#f6f8fa;padding:6px 12px;border-bottom:1px solid #d0d7de;font-size:11px;color:#57606a;">';
        $html .= '<span style="color:#cf222e;">- before</span>&nbsp;&nbsp;<span style="color:#1a7f37;">+ after</span>';
        $html .= '</div>';
        $html .= $rows;
        $html .= '</div>';

        return new HtmlString($html);
    }

    protected function makeTable(): Table
    {
        return $this->makeBaseTable();
    }
}
