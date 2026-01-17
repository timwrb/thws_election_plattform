<?php

namespace App\Filament\Electives\Resources\Fwpms;

use App\Filament\Electives\Resources\Fwpms\Pages\ActiveCourses;
use App\Filament\Electives\Resources\Fwpms\Pages\EnrollInCourses;
use App\Filament\Electives\Resources\Fwpms\Pages\ListFwpms;
use App\Filament\Electives\Resources\Fwpms\Tables\FwpmsTable;
use App\Models\Fwpm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FwpmResource extends Resource
{
    protected static ?string $model = Fwpm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|null|\UnitEnum $navigationGroup = 'FWPM';

    protected static ?string $navigationLabel = 'Browse Courses';

    protected static ?int $navigationSort = 20;

    #[\Override]
    public static function table(Table $table): Table
    {
        return FwpmsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFwpms::route('/'),
            'active' => ActiveCourses::route('/active'),
            'enroll' => EnrollInCourses::route('/enroll'),
        ];
    }
}
