<?php
declare(strict_types=1);
namespace Wwwision\DAM\Command;

use Wwwision\DAM\Model\AssetId;
use Wwwision\DAM\Model\TagIds;

/**
 * Command to set all tags of a given asset
 */
final class SetAssetTags implements Command
{
    public function __construct(
        public readonly AssetId $assetId,
        public readonly TagIds $tagIds,
    ) {}
}
