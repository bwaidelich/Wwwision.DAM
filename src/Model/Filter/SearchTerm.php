<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model\Filter;

final class SearchTerm
{
    private function __construct(
        public readonly string $value,
    ) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
