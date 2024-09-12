<?php
declare(strict_types=1);
namespace Wwwision\DAM\Command;

use Wwwision\DAM\Model\FolderId;
use Wwwision\DAM\Model\FolderLabel;

/**
 * Command to add a new folder to the DAM
 */
final class AddFolder implements Command
{
    /**
     * @param ?FolderId $parentFolderId Identifier of the parent folder – if NULL the new folder is added in the "assets" root node
     */
    private function __construct(
        public readonly FolderId $id,
        public readonly FolderLabel $label,
        public readonly ?FolderId $parentFolderId,
    ) {}

    /**
     * @param FolderId|string|null $parentFolderId Identifier of the parent folder – if NULL the new folder is added in the "assets" root node
     */
    public static function create(
        FolderId|string $id,
        FolderLabel|string $label,
        FolderId|string $parentFolderId = null,
    ): self
    {
        if (is_string($id)) {
            $id = FolderId::fromString($id);
        }
        if (is_string($label)) {
            $label = FolderLabel::fromString($label);
        }
        if (is_string($parentFolderId)) {
            $parentFolderId = FolderId::fromString($parentFolderId);
        }
        return new self(
            $id,
            $label,
            $parentFolderId,
        );
    }
}
