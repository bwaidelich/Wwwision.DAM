<?php
declare(strict_types=1);

namespace Wwwision\DAM\Model\Filter;

use Wwwision\DAM\Model\AssetType;
use Wwwision\DAM\Model\FolderId;
use Wwwision\DAM\Model\TagId;

final class AssetFilter
{
    private function __construct(
        public readonly ?AssetType $assetType,
        public readonly ?TagId $tagId,
        public readonly ?FolderId $folderId,
        public readonly ?SearchTerm $searchTerm,
        public readonly ?Ordering $ordering,
        public readonly ?Pagination $pagination,
    ) {
    }

    /**
     * @param Pagination|array{limit?:int,offset?:int}|null $pagination
     */
    public static function create(
        AssetType|string $assetType = null,
        TagId|string $tagId = null,
        FolderId|string $folderId = null,
        SearchTerm|string $searchTerm = null,
        Ordering $ordering = null,
        Pagination|array $pagination = null,
    ): self
    {
        if (is_string($assetType)) {
            $assetType = AssetType::fromString($assetType);
        }
        if (is_string($tagId)) {
            $tagId = TagId::fromString($tagId);
        }
        if (is_string($folderId)) {
            $folderId = FolderId::fromString($folderId);
        }
        if (is_string($searchTerm)) {
            $searchTerm = SearchTerm::fromString($searchTerm);
        }
        if (is_array($pagination)) {
            $pagination = Pagination::fromArray($pagination);
        }
        return new self(
            $assetType,
            $tagId,
            $folderId,
            $searchTerm,
            $ordering,
            $pagination,
        );
    }
}
