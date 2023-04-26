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
    ) {}

    public static function create(): self
    {
        return new self(null, null, null,null, null, null);
    }

    public function with(
        AssetType $assetType = null,
        TagId $tagId = null,
        FolderId $folderId = null,
        SearchTerm $searchTerm = null,
        Ordering $ordering = null,
        Pagination $pagination = null,
    ): self
    {
        return new self(
            $assetType ?? $this->assetType,
            $tagId ?? $this->tagId,
            $folderId ?? $this->folderId,
            $searchTerm ?? $this->searchTerm,
            $ordering ?? $this->ordering,
            $pagination ?? $this->pagination,
        );
    }
}
