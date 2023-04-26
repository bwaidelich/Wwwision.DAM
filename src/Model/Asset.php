<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use JsonSerializable;

/**
 * The main read model representing an asset in the DAM
 */
final class Asset implements JsonSerializable
{
    private function __construct(
        public readonly AssetId $id,
        public readonly AssetType $type,
        public readonly MediaType $mediaType,
        public readonly ResourcePointer $resourcePointer,
        public readonly Filename $filename,
        public readonly Metadata $metadata,
        public readonly AssetLabel $label,
        public readonly ?AssetCaption $caption,
        public readonly ?Dimensions $dimensions,
    ) {}

    public static function create(
        AssetId $id,
        AssetType $type,
        MediaType $mediaType,
        ResourcePointer $resourcePointer,
        Filename $filename,
        Metadata $metadata,
        AssetLabel $label,
        ?AssetCaption $caption,
        ?Dimensions $dimensions,
    ): self {
        return new self($id, $type, $mediaType, $resourcePointer, $filename, $metadata, $label, $caption, $dimensions);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
