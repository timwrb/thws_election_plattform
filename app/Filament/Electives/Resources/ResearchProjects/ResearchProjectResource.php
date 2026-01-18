<?php

namespace App\Filament\Electives\Resources\ResearchProjects;

use App\Filament\Electives\Resources\ResearchProjects\Pages\CreateResearchProject;
use App\Filament\Electives\Resources\ResearchProjects\Pages\ListResearchProjects;
use App\Filament\Electives\Resources\ResearchProjects\Schemas\ResearchProjectForm;
use App\Filament\Electives\Resources\ResearchProjects\Tables\ResearchProjectsTable;
use App\Models\ResearchProject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

class ResearchProjectResource extends Resource
{
    protected static ?string $model = ResearchProject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|null|UnitEnum $navigationGroup = 'Research Projects';

    protected static ?string $navigationLabel = 'Browse Projects';

    protected static ?int $navigationSort = 30;

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return ResearchProjectForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return ResearchProjectsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchProjects::route('/'),
            'create' => CreateResearchProject::route('/create'),
        ];
    }
}
