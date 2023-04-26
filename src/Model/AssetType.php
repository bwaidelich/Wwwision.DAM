<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

enum AssetType
{
    case Audio;
    case Document;
    case Image;
    case Video;

    public static function fromMediaType(MediaType $mediaType): self
    {
        if ($mediaType->value === 'image/x-icon') {
            return self::Document;
        }
        return match ($mediaType->type) {
            MediaTypeType::image => self::Image,
            MediaTypeType::audio => self::Audio,
            MediaTypeType::video => self::Video,
            default => self::Document,
        };
    }

    public function supportsDimensions(): bool
    {
        return in_array($this, [self::Image, self::Video], true);
    }
}
