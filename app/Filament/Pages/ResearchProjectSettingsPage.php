<?php

namespace App\Filament\Pages;

use App\Settings\ResearchProjectSettings;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ResearchProjectSettingsPage extends SettingsPage
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-beaker';

    protected static string $settings = ResearchProjectSettings::class;

    protected static ?string $title = 'Research Project Settings';

    protected static string|null|\UnitEnum $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Research Projects';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin');
    }

    #[\Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Application Configuration')
                    ->description('Configure research project application settings')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('applicationOpen')
                                    ->label('Applications Open')
                                    ->helperText('Enable or disable research project applications globally')
                                    ->default(false)
                                    ->inline(false)
                                    ->columnSpanFull(),

                                Forms\Components\Toggle::make('requireApprovalBeforeCreation')
                                    ->label('Require Approval Before Creation')
                                    ->helperText('New research projects need admin approval before being published')
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Application Schedule')
                    ->description('Configure when students can apply for research projects')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('applicationStartDate')
                                    ->label('Application Start Date')
                                    ->helperText('When students can start applying (leave empty for no restriction)')
                                    ->nullable()
                                    ->native(false),

                                Forms\Components\DateTimePicker::make('applicationEndDate')
                                    ->label('Application End Date')
                                    ->helperText('Application deadline (leave empty for no restriction)')
                                    ->nullable()
                                    ->native(false),
                            ]),
                    ]),

                Section::make('Capacity Management')
                    ->description('Configure default student capacity for research projects')
                    ->schema([
                        Forms\Components\TextInput::make('maxStudentsPerProject')
                            ->label('Maximum Students Per Project')
                            ->helperText('Default maximum students per research project (can be overridden per project)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                    ]),
            ]);
    }
}
