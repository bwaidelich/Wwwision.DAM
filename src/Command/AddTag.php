<?php
declare(strict_types=1);
namespace Wwwision\DAM\Command;

use Wwwision\DAM\Model\TagId;
use Wwwision\DAM\Model\TagLabel;

/**
 * Command to add a new tag to the DAM
 */
final class AddTag implements Command
{
    public function __construct(
        public readonly TagId $id,
        public readonly TagLabel $label,
    ) {}
}
