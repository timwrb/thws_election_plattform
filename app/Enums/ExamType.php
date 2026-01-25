<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ExamType: string implements HasLabel
{
    case Written = 'written';
    case Oral = 'oral';
    case Portfolio = 'portfolio';

    public function getLabel(): string
    {
        return match ($this) {
            self::Written => 'Written Exam',
            self::Oral => 'Oral Exam',
            self::Portfolio => 'Portfolio Assessment',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Written => Heroicon::OutlinedPencilSquare,
            self::Oral => Heroicon::OutlinedChatBubbleLeftRight,
            self::Portfolio => Heroicon::OutlinedFolderOpen,
        };
    }

    public function getShortLabel(): string
    {
        return match ($this) {
            self::Written => 'Written',
            self::Oral => 'Oral',
            self::Portfolio => 'Portfolio',
        };
    }

    public static function fromGermanType(string $germanType): self
    {
        // Handle combined types (e.g., "Kolloquium#Praktische Studienleistung")
        // Take the first type if multiple exist
        $firstType = explode('#', $germanType)[0];
        $trimmedType = trim($firstType);

        // Translate German exam type to English label
        $translatedType = __($trimmedType);

        return match ($translatedType) {
            'Written Exam' => self::Written,
            'Oral Exam' => self::Oral,
            'Portfolio Assessment' => self::Portfolio,
            default => self::Written, // Default fallback
        };
    }
}
