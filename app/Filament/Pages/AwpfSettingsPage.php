<?php

namespace App\Filament\Pages;

use App\Settings\AwpfSettings;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Override;

class AwpfSettingsPage extends SettingsPage
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-academic-cap';

    protected static string $settings = AwpfSettings::class;

    protected static ?string $title = 'AWPF Settings';

    protected static string|null|\UnitEnum $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'AWPF Settings';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('super_admin');
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Enrollment Configuration')
                    ->description('Configure AWPF course enrollment settings for students')
                    ->schema([
                        Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('maxSelections')
                                    ->label('Maximum Selections')
                                    ->helperText('Maximum number of AWPF courses a student can select')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(10)
                                    ->default(2),

                                Forms\Components\TextInput::make('minRequiredSelections')
                                    ->label('Minimum Required Selections')
                                    ->helperText('Minimum number of AWPF courses a student must select')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10)
                                    ->default(1),
                            ]),

                        Forms\Components\Toggle::make('enrollmentOpen')
                            ->label('Enrollment Open')
                            ->helperText('Enable or disable AWPF enrollment globally')
                            ->default(false)
                            ->inline(false),
                    ]),

                Section::make('Enrollment Schedule')
                    ->description('Configure when students can enroll in AWPF courses')
                    ->schema([
                        Grid::make()
                            ->schema([
                                Forms\Components\DateTimePicker::make('enrollmentStartDate')
                                    ->label('Enrollment Start Date')
                                    ->helperText('When students can start enrolling (leave empty for no restriction)')
                                    ->nullable()
                                    ->native(false),

                                Forms\Components\DateTimePicker::make('enrollmentEndDate')
                                    ->label('Enrollment End Date')
                                    ->helperText('Enrollment deadline (leave empty for no restriction)')
                                    ->nullable()
                                    ->native(false),
                            ]),
                    ]),

                Section::make('Capacity Management')
                    ->description('Configure course capacity limits')
                    ->schema([
                        Forms\Components\TextInput::make('maxStudentsPerCourse')
                            ->label('Maximum Students Per Course')
                            ->helperText('Global capacity limit for all AWPF courses (leave empty for unlimited)')
                            ->numeric()
                            ->nullable()
                            ->minValue(1),
                    ]),
            ]);
    }
}
