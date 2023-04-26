<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use JsonSerializable;
use Ramsey\Uuid\Uuid;

/**
 * Unique identifier of a folder in the DAM
 */
final class FolderId implements JsonSerializable
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

    public static function root(): self
    {
        return new self('__root__');
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
