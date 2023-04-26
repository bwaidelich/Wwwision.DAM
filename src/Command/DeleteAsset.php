<?php
declare(strict_types=1);
namespace Wwwision\DAM\Command;

use Wwwision\DAM\Model\AssetId;

/**
 * Command to remove an asset from the DAM
 */
final class DeleteAsset implements Command
{
    public function __construct(
        public readonly AssetId $id,
    ) {}
}
