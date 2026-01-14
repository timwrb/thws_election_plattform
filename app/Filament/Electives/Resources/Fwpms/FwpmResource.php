<?php

namespace App\Filament\Electives\Resources\Fwpms;

use App\Filament\Electives\Resources\Fwpms\Pages\EnrollInCourses;
use App\Filament\Electives\Resources\Fwpms\Pages\ListFwpms;
use App\Filament\Electives\Resources\Fwpms\Pages\MyCourses;
use App\Filament\Electives\Resources\Fwpms\Schemas\FwpmForm;
use App\Filament\Electives\Resources\Fwpms\Tables\FwpmsTable;
use App\Models\Fwpm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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
    public static function form(Schema $schema): Schema
    {
        return FwpmForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return FwpmsTable::configure($table);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFwpms::route('/'),
            'my-courses' => MyCourses::route('/my-courses'),
            'enroll' => EnrollInCourses::route('/enroll'),
        ];
    }
}
