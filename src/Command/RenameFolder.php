<?php
declare(strict_types=1);
namespace Wwwision\DAM\Command;

use Wwwision\DAM\Model\FolderId;
use Wwwision\DAM\Model\FolderLabel;

/**
 * Command to rename a folder
 */
final class RenameFolder implements Command
{
    public function __construct(
        public readonly FolderId $id,
        public readonly FolderLabel $newLabel,
    ) {}
}
