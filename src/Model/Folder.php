<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use JsonSerializable;

final class Folder implements JsonSerializable
{
    private function __construct(
        public readonly FolderId $id,
        public readonly FolderLabel $label,
    ) {}

    public static function create(FolderId $id, FolderLabel $label): self
    {
        return new self($id, $label);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
