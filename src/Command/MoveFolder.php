<?php
declare(strict_types=1);
namespace Wwwision\DAM\Command;

use Wwwision\DAM\Model\FolderId;

/**
 * Command to move a folder to a new location
 */
final class MoveFolder implements Command
{
    /**
     * @param ?FolderId $newParentFolderId Identifier of the folder to move the folder into – if NULL, the asset will be moved to the "assets" root node
     */
    public function __construct(
        public readonly FolderId $id,
        public readonly ?FolderId $newParentFolderId,
    ) {}
}
