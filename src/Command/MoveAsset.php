<?php
declare(strict_types=1);
namespace Wwwision\DAM\Command;

use Wwwision\DAM\Model\AssetId;
use Wwwision\DAM\Model\FolderId;

/**
 * Command to move an asset to a new folder
 */
final class MoveAsset implements Command
{
    /**
     * @param ?FolderId $newParentFolderId Identifier of the folder to move the asset to – if NULL, the asset will be moved to the "assets" root node
     */
    public function __construct(
        public readonly AssetId $assetId,
        public readonly ?FolderId $newParentFolderId,
    ) {}
}
