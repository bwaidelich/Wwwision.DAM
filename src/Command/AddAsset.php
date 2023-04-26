<?php
declare(strict_types=1);
namespace Wwwision\DAM\Command;

use Wwwision\DAM\Model\AssetCaption;
use Wwwision\DAM\Model\AssetId;
use Wwwision\DAM\Model\AssetLabel;
use Wwwision\DAM\Model\Filename;
use Wwwision\DAM\Model\FolderId;
use Wwwision\DAM\Model\MediaType;
use Wwwision\DAM\Model\Metadata;
use Wwwision\DAM\Model\ResourcePointer;
use Wwwision\DAM\Model\Dimensions;
use Wwwision\DAM\Model\TagIds;

/**
 * Command to register an asset in the DAM
 */
final class AddAsset implements Command
{
    public function __construct(
        public readonly AssetId $id,
        public readonly MediaType $mediaType,
        public readonly ResourcePointer $resourcePointer,
        public readonly Filename $filename,
        public readonly Metadata $metadata,
        public readonly AssetLabel $label,
        public readonly ?AssetCaption $caption,
        public readonly ?Dimensions $dimensions,
        public readonly ?FolderId $folderId,
        public readonly TagIds $initialTags,
    ) {}
}
