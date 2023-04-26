<?php
declare(strict_types=1);
namespace Wwwision\DAM\Command;

use Wwwision\DAM\Model\TagId;

/**
 * Command to delete a tag from the DAM
 * NOTE: This won't delete the related assets
 */
final class DeleteTag implements Command
{
    public function __construct(
        public readonly TagId $id,
    ) {}
}
