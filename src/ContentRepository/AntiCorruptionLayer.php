<?php
declare(strict_types=1);
namespace Wwwision\DAM\ContentRepository;

use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\CountBackReferencesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\CountChildNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\CountReferencesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindBackReferencesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindReferencesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\Ordering\Ordering;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\Ordering\OrderingDirection as CrOrderingDirection;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\Ordering\TimestampField;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\Pagination\Pagination;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\Projection\ContentGraph\Nodes;
use Neos\ContentRepository\Core\Projection\ContentGraph\Subtree;
use Neos\ContentRepository\Core\SharedModel\Node\PropertyName;
use UnexpectedValueException;
use Wwwision\DAM\Model\Asset;
use Wwwision\DAM\Model\AssetCaption;
use Wwwision\DAM\Model\AssetId;
use Wwwision\DAM\Model\Assets;
use Wwwision\DAM\Model\AssetType;
use Wwwision\DAM\Model\Filter\AssetFilter;
use Wwwision\DAM\Model\Filter\OrderingDirection;
use Wwwision\DAM\Model\Filter\OrderingField;
use Wwwision\DAM\Model\Folder;
use Wwwision\DAM\Model\FolderId;
use Wwwision\DAM\Model\FolderLabel;
use Wwwision\DAM\Model\Folders;
use Wwwision\DAM\Model\FolderTree;
use Wwwision\DAM\Model\FolderWithChildren;
use Wwwision\DAM\Model\Tag;
use Wwwision\DAM\Model\TagId;
use Wwwision\DAM\Model\Tags;
use function date_date_set;
use function dd;

final class AntiCorruptionLayer
{

    public static function applyAssetFilter(FindChildNodesFilter|CountChildNodesFilter|FindReferencesFilter|CountReferencesFilter|FindBackReferencesFilter|CountBackReferencesFilter $nodeFilter, AssetFilter $assetFilter): FindChildNodesFilter|CountChildNodesFilter|FindReferencesFilter|CountReferencesFilter|FindBackReferencesFilter|CountBackReferencesFilter
    {
        if ($assetFilter->assetType !== null) {
            $nodeFilter = $nodeFilter->with(
                nodeTypes: self::assetTypeToNodeTypeName($assetFilter->assetType)->value,
            );
        } else {
            $nodeFilter = $nodeFilter->with(
                nodeTypes: AssetNodeTypes::Asset->name()->value,
            );
        }
        if ($assetFilter->searchTerm !== null) {
            $nodeFilter = $nodeFilter->with(searchTerm: $assetFilter->searchTerm->value);
        }
        if ($assetFilter->ordering !== null && property_exists($nodeFilter, 'ordering')) {
            $direction = $assetFilter->ordering->direction === OrderingDirection::DESCENDING ? CrOrderingDirection::DESCENDING : CrOrderingDirection::ASCENDING;
            $nodeFilter = $nodeFilter->with(
                ordering: $assetFilter->ordering->field === OrderingField::LAST_MODIFIED
                    ? Ordering::byTimestampField(TimestampField::ORIGINAL_LAST_MODIFIED, $direction)->andByTimestampField(TimestampField::CREATED, $direction)
                    : Ordering::byProperty(PropertyName::fromString('label'), $direction),
            );
        }
        if (property_exists($nodeFilter, 'pagination')) {
            if ($assetFilter->pagination !== null) {
                $nodeFilter = $nodeFilter->with(pagination: Pagination::fromLimitAndOffset($assetFilter->pagination->limit, $assetFilter->pagination->offset));
            } else {
                $nodeFilter = $nodeFilter->with(pagination: Pagination::fromLimitAndOffset(20, 0));
            }
        }
        return $nodeFilter;
    }

    public static function assetsFromNodes(Nodes $nodes): Assets
    {
        return Assets::create(...array_map(self::assetFromNode(...), iterator_to_array($nodes)));
    }

    public static function assetFromNode(Node $node): Asset
    {
        $assetValues = [
            'id' => AssetId::fromString($node->aggregateId->value),
        ];
        foreach ($node->properties as $propertyName => $propertyValue) {
            $assetValues[$propertyName] = $node->getProperty($propertyName);
        }
        $assetValues['type'] = self::nodeTypeNameToAssetType($node->nodeTypeName);
        if (!isset($assetValues['dimensions'])) {
            $assetValues['dimensions'] = null;
        }
        if (!isset($assetValues['caption'])) {
            $assetValues['caption'] = AssetCaption::fromString('');
        }
        return Asset::create(...$assetValues);
    }

    public static function tagsFromNodes(Nodes $nodes): Tags
    {
        return Tags::create(...array_map(self::tagFromNode(...), iterator_to_array($nodes)));
    }

    public static function tagFromNode(Node $node): Tag
    {
        $tagValues = [
            'id' => TagId::fromString($node->aggregateId->value),
            ...$node->properties,
        ];
        return Tag::create(...$tagValues);
    }

    public static function folderWithChildrenFromSubtree(Subtree $folderSubtree): FolderWithChildren
    {
        return FolderWithChildren::create(
            self::folderFromNode($folderSubtree->node),
            array_map(self::folderWithChildrenFromSubtree(...), $folderSubtree->children),
        );
    }

    public static function foldersFromNodes(Nodes $nodes): Folders
    {
        return Folders::create(...array_map(self::folderFromNode(...), iterator_to_array($nodes)));
    }

    public static function folderFromNode(Node $node): Folder
    {
        return Folder::create(
            id: FolderId::fromString($node->aggregateId->value),
            label: $node->getProperty('label') ?? FolderLabel::fromString('root'),
        );
    }

    public static function assetTypeToNodeTypeName(AssetType $assetType): NodeTypeName
    {
        return match ($assetType) {
            AssetType::Audio => AssetNodeTypes::Audio->name(),
            AssetType::Document => AssetNodeTypes::Document->name(),
            AssetType::Image => AssetNodeTypes::Image->name(),
            AssetType::Video => AssetNodeTypes::Video->name(),
        };
    }

    public static function nodeTypeNameToAssetType(NodeTypeName $nodeTypeName): AssetType
    {
        return match ($nodeTypeName) {
            AssetNodeTypes::Audio->name() => AssetType::Audio,
            AssetNodeTypes::Document->name() => AssetType::Document,
            AssetNodeTypes::Image->name() => AssetType::Image,
            AssetNodeTypes::Video->name() => AssetType::Video,
            default => throw new UnexpectedValueException(sprintf('Unexpected asset node type "%s"', $nodeTypeName->value), 1680773479),
        };
    }
}
