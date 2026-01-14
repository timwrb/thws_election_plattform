<?php

namespace App\Filament\Electives\Resources\Awpfs;

use App\Filament\Electives\Resources\Awpfs\Pages\EnrollInCourses;
use App\Filament\Electives\Resources\Awpfs\Pages\ListAwpfs;
use App\Filament\Electives\Resources\Awpfs\Pages\MyCourses;
use App\Filament\Electives\Resources\Awpfs\Schemas\AwpfForm;
use App\Filament\Electives\Resources\Awpfs\Tables\AwpfsTable;
use App\Models\Awpf;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AwpfResource extends Resource
{
    protected static ?string $model = Awpf::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|null|\UnitEnum $navigationGroup = 'AWPF';

    protected static ?string $navigationLabel = 'Browse Courses';

    protected static ?int $navigationSort = 10;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return AwpfForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return AwpfsTable::configure($table);
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
            'index' => ListAwpfs::route('/'),
            'my-courses' => MyCourses::route('/my-courses'),
            'enroll' => EnrollInCourses::route('/enroll'),
        ];
    }
}
