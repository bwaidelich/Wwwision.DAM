<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use JsonSerializable;
use Ramsey\Uuid\Uuid;

/**
 * Unique identifier of an asset in the DAM
 */
final class AssetId implements JsonSerializable
{
    private function __construct(
        public readonly string $value,
    ) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function create(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
