<?php

namespace App\Enum;

enum ProjectRendererEnum: string
{
    case SIMPLE_PANORAMA = 'simple_panorama';
    case GALLERY = 'gallery';
    case VIRTUAL_VISIT = 'virtual_visit';

    public static function getChoices(): array
    {
        return [
            'Simple panorama' => self::SIMPLE_PANORAMA->value,
            'Gallery' => self::GALLERY->value,
            'Virtual visit' => self::VIRTUAL_VISIT->value
        ];
    }

    public static function getEnumCaseByValue(string $value): self
    {
        return match ($value) {
            'simple_panorama' => self::SIMPLE_PANORAMA,
            'gallery' => self::GALLERY,
            'virtual_visit' => self::VIRTUAL_VISIT,
            default => throw new \InvalidArgumentException('Invalid renderer value')
        };
    }
}
