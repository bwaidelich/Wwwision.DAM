<?php
declare(strict_types=1);
namespace Wwwision\DAM\Command;

use Wwwision\DAM\Model\AssetId;
use Wwwision\DAM\Model\TagId;

/**
 * Command to relate an asset to the specified tag
 */
final class AddTagToAsset implements Command
{
    public function __construct(
        public readonly AssetId $assetId,
        public readonly TagId $tagId,
    ) {}
}
