<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use JsonSerializable;

final class Tag implements JsonSerializable
{
    private function __construct(
        public readonly TagId $id,
        public readonly TagLabel $label,
    ) {}

    public static function create(TagId $id, TagLabel $label): self
    {
        return new self($id, $label);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
