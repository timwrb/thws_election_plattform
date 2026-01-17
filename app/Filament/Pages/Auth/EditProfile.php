<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    #[\Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Avatar'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->hiddenLabel()
                            ->collection('avatars')
                            ->avatar()
                            ->circleCropper()
                            ->alignCenter(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('Profile Information'))
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ])
                    ->columnSpanFull(),
                Section::make(__('Password'))
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
