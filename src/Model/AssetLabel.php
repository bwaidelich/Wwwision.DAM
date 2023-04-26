<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use JsonSerializable;

/**
 * Human-readable label of an asset
 */
final class AssetLabel implements JsonSerializable
{
    private function __construct(
        public readonly string $value,
    ) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
