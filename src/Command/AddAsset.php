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
use Wwwision\DAM\Model\TagId;
use Wwwision\DAM\Model\TagIds;

/**
 * Command to register an asset in the DAM
 */
final class AddAsset implements Command
{
    private function __construct(
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

    /**
     * @param Metadata|array<string,mixed>|null $metadata
     * @param Dimensions|array{width: int, height: int}|null $dimensions
     * @param TagIds|array<string|TagId>|null $initialTags
     */
    public static function create(
        AssetId|string $id,
        MediaType|string $mediaType,
        ResourcePointer|string $resourcePointer,
        Filename|string $filename,
        AssetLabel|string $label,
        Metadata|array|null $metadata = null,
        AssetCaption|string|null $caption = null,
        Dimensions|array|null $dimensions = null,
        FolderId|string|null $folderId = null,
        TagIds|array|null $initialTags = null,
    ): self
    {
        if (is_string($id)) {
            $id = AssetId::fromString($id);
        }
        if (is_string($mediaType)) {
            $mediaType = MediaType::fromString($mediaType);
        }
        if (is_string($resourcePointer)) {
            $resourcePointer = ResourcePointer::fromString($resourcePointer);
        }
        if (is_string($filename)) {
            $filename = Filename::fromString($filename);
        }
        if (is_string($label)) {
            $label = AssetLabel::fromString($label);
        }
        if (is_array($metadata)) {
            $metadata = Metadata::fromArray($metadata);
        } elseif ($metadata === null) {
            $metadata = Metadata::none();
        }
        if (is_string($caption)) {
            $caption = AssetCaption::fromString($caption);
        }
        if (is_array($dimensions)) {
            $dimensions = Dimensions::fromArray($dimensions);
        }
        if (is_string($folderId)) {
            $folderId = FolderId::fromString($folderId);
        }
        if (is_array($initialTags)) {
            $initialTags = TagIds::fromArray($initialTags);
        } elseif ($initialTags === null) {
            $initialTags = TagIds::createEmpty();
        }
        return new self(
            $id,
            $mediaType,
            $resourcePointer,
            $filename,
            $metadata,
            $label,
            $caption,
            $dimensions,
            $folderId,
            $initialTags,
        );
    }
}
