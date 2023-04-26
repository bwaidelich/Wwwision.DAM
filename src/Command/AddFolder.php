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
    public function __construct(
        public readonly FolderId $id,
        public readonly FolderLabel $label,
        public readonly ?FolderId $parentFolderId,
    ) {}
}
