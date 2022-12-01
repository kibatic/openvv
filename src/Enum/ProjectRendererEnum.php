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
}
