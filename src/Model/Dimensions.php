<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use JsonSerializable;

/**
 * Dimensions (aka size) of an asset (only applicable to Image and Video assets)
 */
final class Dimensions implements JsonSerializable
{
    private function __construct(
        public readonly int $width,
        public readonly int $height,
    ) {}

    public static function fromWidthAndHeight(int $width, int $height): self
    {
        return new self($width, $height);
    }

    public static function fromArray(array $array): self
    {
        return new self((int)$array['width'], (int)$array['height']);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
