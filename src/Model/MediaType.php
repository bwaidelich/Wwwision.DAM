<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use JsonSerializable;

final class MediaType implements JsonSerializable
{
    public readonly MediaTypeType $type;

    private function __construct(
        public readonly string $value,
    ) {
        if (preg_match('/(?<type>\w+)\/[-+.\w]+/', $this->value, $matches) !== 1) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid media type', $this->value), 1681900702);
        }
        $this->type = MediaTypeType::from($matches['type']);

    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
