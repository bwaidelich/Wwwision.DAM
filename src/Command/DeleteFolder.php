<?php
declare(strict_types=1);
namespace Wwwision\DAM\Command;

use Wwwision\DAM\Model\FolderId;

/**
 * Command to remove a folder from the DAM
 * NOTE: This will remove all sub-folders and assets contained in those!
 */
final class DeleteFolder implements Command
{
    public function __construct(
        public readonly FolderId $id,
    ) {}
}
