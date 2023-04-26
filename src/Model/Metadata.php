<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use JsonSerializable;

final class Metadata implements JsonSerializable
{
    private function __construct(
        public readonly array $value,
    ) {}

    public static function fromArray(array $array): self
    {
        return new self($array);
    }

    public static function none(): self
    {
        return new self([]);
    }

    public function jsonSerialize(): array
    {
        return $this->value;
    }
}
