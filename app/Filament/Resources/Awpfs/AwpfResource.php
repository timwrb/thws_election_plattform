<?php

namespace App\Filament\Resources\Awpfs;

use App\Filament\Resources\Awpfs\Pages\CreateAwpf;
use App\Filament\Resources\Awpfs\Pages\EditAwpf;
use App\Filament\Resources\Awpfs\Pages\ListAwpfs;
use App\Filament\Resources\Awpfs\Pages\ViewAwpf;
use App\Filament\Resources\Awpfs\Schemas\AwpfForm;
use App\Filament\Resources\Awpfs\Schemas\AwpfInfolist;
use App\Filament\Resources\Awpfs\Tables\AwpfsTable;
use App\Models\Awpf;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class AwpfResource extends Resource
{
    protected static ?string $model = Awpf::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'AWPF';

    protected static ?string $modelLabel = 'AWPF';

    protected static ?string $pluralModelLabel = 'AWPF Courses';

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return AwpfForm::configure($schema);
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return AwpfInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return AwpfsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAwpfs::route('/'),
            'create' => CreateAwpf::route('/create'),
            'view' => ViewAwpf::route('/{record}'),
            'edit' => EditAwpf::route('/{record}/edit'),
        ];
    }
}
