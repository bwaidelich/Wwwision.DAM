<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model\Filter;

final class Ordering
{
    private function __construct(
        public readonly OrderingField $field,
        public readonly OrderingDirection $direction,
    ) {}

    public static function by(OrderingField $field, OrderingDirection $direction): self
    {
        return new self($field, $direction);
    }

}
