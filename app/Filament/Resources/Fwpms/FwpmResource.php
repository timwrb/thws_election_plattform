<?php

namespace App\Filament\Resources\Fwpms;

use App\Filament\Resources\Fwpms\Pages\CreateFwpm;
use App\Filament\Resources\Fwpms\Pages\EditFwpm;
use App\Filament\Resources\Fwpms\Pages\ListFwpms;
use App\Filament\Resources\Fwpms\Pages\ViewFwpm;
use App\Filament\Resources\Fwpms\Schemas\FwpmForm;
use App\Filament\Resources\Fwpms\Schemas\FwpmInfolist;
use App\Filament\Resources\Fwpms\Tables\FwpmsTable;
use App\Models\Fwpm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class FwpmResource extends Resource
{
    protected static ?string $model = Fwpm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'FWPM';

    protected static ?string $modelLabel = 'FWPM';

    protected static ?string $pluralModelLabel = 'FWPM Courses';

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return FwpmForm::configure($schema);
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return FwpmInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return FwpmsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFwpms::route('/'),
            'create' => CreateFwpm::route('/create'),
            'view' => ViewFwpm::route('/{record}'),
            'edit' => EditFwpm::route('/{record}/edit'),
        ];
    }
}
