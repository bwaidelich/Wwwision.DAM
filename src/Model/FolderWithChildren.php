<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

final class FolderWithChildren
{
    /**
     * @param array<self> $children
     */
    private function __construct(
        public readonly Folder $folder,
        public readonly array $children,
    ) {}

    /**
     * @param array<self> $children
     */
    public static function create(Folder $folder, array $children): self
    {
        return new self($folder, $children);
    }
}
