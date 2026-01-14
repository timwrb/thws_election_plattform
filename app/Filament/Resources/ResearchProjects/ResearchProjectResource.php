<?php

namespace App\Filament\Resources\ResearchProjects;

use App\Filament\Resources\ResearchProjects\Pages\CreateResearchProject;
use App\Filament\Resources\ResearchProjects\Pages\EditResearchProject;
use App\Filament\Resources\ResearchProjects\Pages\ListResearchProjects;
use App\Filament\Resources\ResearchProjects\Pages\ViewResearchProject;
use App\Filament\Resources\ResearchProjects\Schemas\ResearchProjectForm;
use App\Filament\Resources\ResearchProjects\Schemas\ResearchProjectInfolist;
use App\Filament\Resources\ResearchProjects\Tables\ResearchProjectsTable;
use App\Models\ResearchProject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResearchProjectResource extends Resource
{
    protected static ?string $model = ResearchProject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $navigationLabel = 'Research Projects';

    protected static ?string $modelLabel = 'Research Project';

    protected static ?string $pluralModelLabel = 'Research Projects';

    protected static ?string $recordTitleAttribute = 'title';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return ResearchProjectForm::configure($schema);
    }

    #[\Override]
    public static function infolist(Schema $schema): Schema
    {
        return ResearchProjectInfolist::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ResearchProjectsTable::configure($table);
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
            'index' => ListResearchProjects::route('/'),
            'create' => CreateResearchProject::route('/create'),
            'view' => ViewResearchProject::route('/{record}'),
            'edit' => EditResearchProject::route('/{record}/edit'),
        ];
    }
}
