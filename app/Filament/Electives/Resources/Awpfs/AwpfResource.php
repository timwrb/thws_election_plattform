<?php

namespace App\Filament\Electives\Resources\Awpfs;

use App\Filament\Electives\Resources\Awpfs\Pages\ActiveCourses;
use App\Filament\Electives\Resources\Awpfs\Pages\EnrollInCourses;
use App\Filament\Electives\Resources\Awpfs\Pages\ListAwpfs;
use App\Filament\Electives\Resources\Awpfs\Tables\AwpfsTable;
use App\Models\Awpf;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class AwpfResource extends Resource
{
    protected static ?string $model = Awpf::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|null|\UnitEnum $navigationGroup = 'AWPF';

    protected static ?string $navigationLabel = 'Browse Courses';

    protected static ?int $navigationSort = 10;

    #[Override]
    public static function table(Table $table): Table
    {
        return AwpfsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAwpfs::route('/'),
            'active' => ActiveCourses::route('/active'),
            'enroll' => EnrollInCourses::route('/enroll'),
        ];
    }
}
