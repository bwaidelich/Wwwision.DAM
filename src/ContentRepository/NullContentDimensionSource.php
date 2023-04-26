<?php
declare(strict_types=1);
namespace Wwwision\DAM\ContentRepository;

use Neos\ContentRepository\Core\Dimension\ContentDimension;
use Neos\ContentRepository\Core\Dimension\ContentDimensionId;
use Neos\ContentRepository\Core\Dimension\ContentDimensionSourceInterface;

final class NullContentDimensionSource implements ContentDimensionSourceInterface
{

    public function getDimension(ContentDimensionId $dimensionId): ?ContentDimension
    {
        return null;
    }

    public function getContentDimensionsOrderedByPriority(): array
    {
        return [];
    }
}
