<?php
declare(strict_types=1);
namespace Wwwision\DAM;

use Neos\ContentRepository\Core\ContentRepository;
use Neos\ContentRepository\Core\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Core\DimensionSpace\OriginDimensionSpacePoint;
use Neos\ContentRepository\Core\Feature\ContentStreamCreation\Command\CreateContentStream;
use Neos\ContentRepository\Core\Feature\NodeCreation\Command\CreateNodeAggregateWithNode;
use Neos\ContentRepository\Core\Feature\NodeModification\Command\SetNodeProperties;
use Neos\ContentRepository\Core\Feature\NodeModification\Dto\PropertyValuesToWrite;
use Neos\ContentRepository\Core\Feature\NodeMove\Command\MoveNodeAggregate;
use Neos\ContentRepository\Core\Feature\NodeMove\Dto\RelationDistributionStrategy;
use Neos\ContentRepository\Core\Feature\NodeReferencing\Command\SetNodeReferences;
use Neos\ContentRepository\Core\Feature\NodeReferencing\Dto\NodeReferencesToWrite;
use Neos\ContentRepository\Core\Feature\NodeRemoval\Command\RemoveNodeAggregate;
use Neos\ContentRepository\Core\Feature\RootNodeCreation\Command\CreateRootNodeAggregateWithNode;
use Neos\ContentRepository\Core\Feature\WorkspaceCreation\Command\CreateRootWorkspace;
use Neos\ContentRepository\Core\Feature\WorkspaceCreation\Command\CreateWorkspace;
use Neos\ContentRepository\Core\Projection\ContentGraph\ContentSubgraphInterface;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\CountChildNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindBackReferencesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindDescendantNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindReferencesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindSubtreeFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\VisibilityConstraints;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateIds;
use Neos\ContentRepository\Core\SharedModel\Node\NodeVariantSelectionStrategy;
use Neos\ContentRepository\Core\SharedModel\Node\ReferenceName;
use Neos\ContentRepository\Core\SharedModel\Workspace\ContentStreamId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceDescription;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceTitle;
use Wwwision\DAM\Command\AddAsset;
use Wwwision\DAM\Command\AddFolder;
use Wwwision\DAM\Command\AddTag;
use Wwwision\DAM\Command\AddTagToAsset;
use Wwwision\DAM\Command\Command;
use Wwwision\DAM\Command\DeleteAsset;
use Wwwision\DAM\Command\DeleteFolder;
use Wwwision\DAM\Command\DeleteTag;
use Wwwision\DAM\Command\MoveAsset;
use Wwwision\DAM\Command\MoveFolder;
use Wwwision\DAM\Command\RenameFolder;
use Wwwision\DAM\Command\RenameTag;
use Wwwision\DAM\Command\SetAssetTags;
use Wwwision\DAM\ContentRepository\AntiCorruptionLayer;
use Wwwision\DAM\ContentRepository\AssetNodeTypes;
use Wwwision\DAM\Model\Asset;
use Wwwision\DAM\Model\AssetId;
use Wwwision\DAM\Model\Assets;
use Wwwision\DAM\Model\AssetType;
use Wwwision\DAM\Model\Filter\AssetFilter;
use Wwwision\DAM\Model\Folder;
use Wwwision\DAM\Model\FolderId;
use Wwwision\DAM\Model\Folders;
use Wwwision\DAM\Model\FolderWithChildren;
use Wwwision\DAM\Model\Tag;
use Wwwision\DAM\Model\TagId;
use Wwwision\DAM\Model\Tags;
use function date_date_set;

/**
 * The central authority to query or mutate the DAM
 */
final class DAM
{
    private ?ContentSubgraphInterface $contentSubgraphRuntimeCache = null;

    public function __construct(
        private readonly ContentRepository $contentRepository,
    ) {
    }

    /**
     * Create required database tables and root nodes
     */
    public function setup(): void
    {
        $this->contentRepository->setUp();
        $workspaceName = WorkspaceName::forLive();
        $csId = ContentStreamId::fromString('live');

        if ($this->contentRepository->getWorkspaceFinder()->findOneByName($workspaceName) === null) {
            $this->contentRepository->handle(CreateRootWorkspace::create($workspaceName, WorkspaceTitle::fromString('DAM Live Workspace'), WorkspaceDescription::fromString(''), $csId));
        }

        $tagsNode = $this->subgraph()->findNodeById(self::tagsNodeId());
        if ($tagsNode === null) {
            $this->contentRepository->handle(CreateRootNodeAggregateWithNode::create(
                $workspaceName,
                self::tagsNodeId(),
                AssetNodeTypes::Tags->name(),
            ));
        }

        $assetsRootNode = $this->subgraph()->findNodeById(self::assetsRootNodeId());
        if ($assetsRootNode === null) {
            $this->contentRepository->handle(CreateRootNodeAggregateWithNode::create(
                $workspaceName,
                self::assetsRootNodeId(),
                AssetNodeTypes::Assets->name(),
            ));
        }
    }

    /**
     * Find an asset by its ID. Returns NULL if the asset could not be found
     */
    public function findAssetById(AssetId $assetId): ?Asset
    {
        $assetNode = $this->subgraph()->findNodeById(NodeAggregateId::fromString($assetId->value));
        if ($assetNode === null || AssetNodeTypes::isAssetNodeType($assetNode->nodeTypeName)) {
            return null;
        }
        return AntiCorruptionLayer::assetFromNode($assetNode);
    }

    /**
     * Find assets by a filter
     *
     * Example:
     *
     * $filter = AssetFilter::create()->with(
     *   assetType: AssetType::Document,
     *   searchTerm: SearchTerm::fromString('some search term'),
     * );
     * $dam->findAssets($filter);
     *
     */
    public function findAssets(AssetFilter $filter): Assets
    {
        if ($filter->tagId !== null) {
            $references = $this->subgraph()
                ->findBackReferences(
                    NodeAggregateId::fromString($filter->tagId->value),
                    AntiCorruptionLayer::applyAssetFilter(FindBackReferencesFilter::create(), $filter)->with(referenceName: 'tags'),
                );
            $assetNodes = $references->getNodes();
        } elseif ($filter->folderId !== null) {
            $assetNodes = $this->subgraph()
                ->findChildNodes(
                    NodeAggregateId::fromString($filter->folderId->value),
                    AntiCorruptionLayer::applyAssetFilter(FindChildNodesFilter::create(), $filter)
                );
        } else {
            $assetNodes = $this->subgraph()
                ->findChildNodes(
                    self::assetsRootNodeId(),
                    AntiCorruptionLayer::applyAssetFilter(FindChildNodesFilter::create(), $filter)
                );
        }
        return AntiCorruptionLayer::assetsFromNodes($assetNodes);
    }

    /**
     * Count assets that match the given filter
     */
    public function countAssets(AssetFilter $filter): int
    {
        return $this->subgraph()
            ->countChildNodes(
                $filter->folderId === null ? self::assetsRootNodeId() : NodeAggregateId::fromString($filter->folderId->value),
                AntiCorruptionLayer::applyAssetFilter(CountChildNodesFilter::create(), $filter),
            );
    }

    /**
     * Return all tags
     */
    public function findTags(): Tags
    {
        $tagNodes = $this->subgraph()->findChildNodes(self::tagsNodeId(), FindChildNodesFilter::create());
        return AntiCorruptionLayer::tagsFromNodes($tagNodes);
    }

    /**
     * Find a tag by its id
     * Returns NULL if no corresponding tag could be found
     */
    public function findTagById(TagId $tagId): ?Tag
    {
        $tagNode = $this->subgraph()->findNodeById(NodeAggregateId::fromString($tagId->value));
        if ($tagNode === null || !$tagNode->nodeTypeName->equals(AssetNodeTypes::Tag->name())) {
            return null;
        }
        return AntiCorruptionLayer::tagFromNode($tagNode);
    }

    /**
     * Return the tags that are referenced by the specified asset
     */
    public function findTagsByAssetId(AssetId $id): Tags
    {
        $referenceFilter = FindReferencesFilter::create()->with(referenceName: 'tags');
        $references = $this->subgraph()->findReferences(NodeAggregateId::fromString($id->value), $referenceFilter);
        return AntiCorruptionLayer::tagsFromNodes($references->getNodes());
    }

    /**
     * Return all folders
     */
    public function findFolders(): Folders
    {
        $filter = FindDescendantNodesFilter::create()->with(nodeTypes: AssetNodeTypes::Folder->name()->value);
        $folderNodes = $this->subgraph()->findDescendantNodes(self::assetsRootNodeId(), $filter);
        return AntiCorruptionLayer::foldersFromNodes($folderNodes);
    }

    /**
     * Return all folders
     */
    public function getFolderTree(): FolderWithChildren
    {
        $filter = FindSubtreeFilter::create()->with(nodeTypes: AssetNodeTypes::Folder->name()->value);
        $folderSubtree = $this->subgraph()->findSubtree(self::assetsRootNodeId(), $filter);
        assert($folderSubtree !== null);
        return AntiCorruptionLayer::folderWithChildrenFromSubtree($folderSubtree);
    }

    /**
     * Find a folder by its id
     * Returns NULL if no corresponding folder could be found
     */
    public function findFolderById(FolderId $folderId): ?Folder
    {
        $folderNode = $this->subgraph()->findNodeById(NodeAggregateId::fromString($folderId->value));
        if ($folderNode === null || !$folderNode->nodeTypeName->equals(AssetNodeTypes::Folder->name())) {
            return null;
        }
        return AntiCorruptionLayer::folderFromNode($folderNode);
    }

    /**
     * Return the parent folder of the given asset/folder
     * Returns NULL if the specified asset/folder is located in the "assets" root
     */
    public function findParentFolder(FolderId|AssetId $childFolderId): ?Folder
    {
        $folderNode = $this->subgraph()->findParentNode(NodeAggregateId::fromString($childFolderId->value));
        if ($folderNode === null || $folderNode->aggregateId->equals(self::assetsRootNodeId())) {
            return null;
        }
        return AntiCorruptionLayer::folderFromNode($folderNode);
    }

    /**
     * Return all direct child folder nodes of a given folder
     */
    public function findChildFolders(FolderId $parentFolderId): Folders
    {
        $filter = FindChildNodesFilter::create()->with(nodeTypes: AssetNodeTypes::Folder->name()->value);
        $folderNodes = $this->subgraph()->findChildNodes(NodeAggregateId::fromString($parentFolderId->value), $filter);
        return AntiCorruptionLayer::foldersFromNodes($folderNodes);
    }

    /** ######## MUTATIONS ######## */

    /**
     * Handles one of the {@see Command}s
     */
    public function handle(Command $command): void
    {
        match ($command::class) {
            AddAsset::class => $this->handleAddAsset($command),
            DeleteAsset::class => $this->handleDeleteAsset($command),

            AddTag::class => $this->handleAddTag($command),
            RenameTag::class => $this->handleRenameTag($command),
            AddTagToAsset::class => $this->handleAddTagToAsset($command),
            SetAssetTags::class => $this->handleSetAssetTags($command),
            DeleteTag::class => $this->handleDeleteTag($command),

            AddFolder::class => $this->handleAddFolder($command),
            RenameFolder::class => $this->handleRenameFolder($command),
            MoveFolder::class => $this->handleMoveFolder($command),
            MoveAsset::class => $this->handleMoveAsset($command),
            DeleteFolder::class => $this->handleDeleteFolder($command),
        };
    }

    private function handleAddAsset(AddAsset $command): void
    {
        $assetType = AssetType::fromMediaType($command->mediaType);
        $parentNodeAggregateId = $command->folderId === null ? self::assetsRootNodeId() : NodeAggregateId::fromString($command->folderId->value);

        $initialPropertyValues = [
            'resourcePointer' => $command->resourcePointer,
            'mediaType' => $command->mediaType,
            'filename' => $command->filename,
            'metadata' => $command->metadata,
            'label' => $command->label,
            'caption' => $command->caption,
        ];
        if ($assetType->supportsDimensions()) {
            $initialPropertyValues['dimensions'] = $command->dimensions;
        }

        $this->contentRepository->handle(CreateNodeAggregateWithNode::create(
            WorkspaceName::forLive(),
            NodeAggregateId::fromString($command->id->value),
            AntiCorruptionLayer::assetTypeToNodeTypeName($assetType),
            OriginDimensionSpacePoint::fromArray([]),
            $parentNodeAggregateId,
            initialPropertyValues: PropertyValuesToWrite::fromArray($initialPropertyValues),
        ));

        if (!$command->initialTags->empty()) {
            $this->handleSetAssetTags(new SetAssetTags($command->id, $command->initialTags));
        }
    }

    private function handleDeleteAsset(DeleteAsset $command): void
    {
        $folder = $this->findParentFolder($command->id);
        $folderId = $folder !== null ? $folder->id : self::assetsRootNodeId();
        $this->contentRepository->handle(RemoveNodeAggregate::create(
            WorkspaceName::forLive(),
            NodeAggregateId::fromString($command->id->value),
            DimensionSpacePoint::fromArray([]),
            NodeVariantSelectionStrategy::STRATEGY_ALL_VARIANTS,
        ));
    }

    private function handleAddTag(AddTag $command): void
    {
        $this->contentRepository->handle(CreateNodeAggregateWithNode::create(
            WorkspaceName::forLive(),
            NodeAggregateId::fromString($command->id->value),
            AssetNodeTypes::Tag->name(),
            OriginDimensionSpacePoint::fromArray([]),
            self::tagsNodeId(),
            initialPropertyValues: PropertyValuesToWrite::fromArray([
                'label' => $command->label,
            ]),
        ));
    }

    private function handleRenameTag(RenameTag $command): void
    {
        $this->contentRepository->handle(SetNodeProperties::create(
            WorkspaceName::forLive(),
            NodeAggregateId::fromString($command->id->value),
            OriginDimensionSpacePoint::fromArray([]),
            PropertyValuesToWrite::fromArray([
                'label' => $command->newLabel,
            ]),
        ));
    }

    private function handleAddTagToAsset(AddTagToAsset $command): void
    {
        $this->contentRepository->handle(SetNodeReferences::create(
            WorkspaceName::forLive(),
            NodeAggregateId::fromString($command->assetId->value),
            OriginDimensionSpacePoint::fromArray([]),
            ReferenceName::fromString('tags'),
            NodeReferencesToWrite::fromNodeAggregateIds(NodeAggregateIds::fromArray([$command->tagId->value]))
        ));
    }

    private function handleSetAssetTags(SetAssetTags $command): void
    {
        $this->contentRepository->handle(SetNodeReferences::create(
            WorkspaceName::forLive(),
            NodeAggregateId::fromString($command->assetId->value),
            OriginDimensionSpacePoint::fromArray([]),
            ReferenceName::fromString('tags'),
            NodeReferencesToWrite::fromNodeAggregateIds(NodeAggregateIds::fromArray($command->tagIds->toStrings()))
        ));
    }

    private function handleDeleteTag(DeleteTag $command): void
    {
        $this->contentRepository->handle(RemoveNodeAggregate::create(
            WorkspaceName::forLive(),
            NodeAggregateId::fromString($command->id->value),
            DimensionSpacePoint::fromArray([]),
            NodeVariantSelectionStrategy::STRATEGY_ALL_VARIANTS,
        ));
    }

    private function handleAddFolder(AddFolder $command): void
    {
        $parentNodeAggregateId = $command->parentFolderId === null ? self::assetsRootNodeId() : NodeAggregateId::fromString($command->parentFolderId->value);
        $this->contentRepository->handle(CreateNodeAggregateWithNode::create(
            WorkspaceName::forLive(),
            NodeAggregateId::fromString($command->id->value),
            AssetNodeTypes::Folder->name(),
            OriginDimensionSpacePoint::fromArray([]),
            $parentNodeAggregateId,
            initialPropertyValues: PropertyValuesToWrite::fromArray([
                'label' => $command->label,
            ]),
        ));
    }

    private function handleRenameFolder(RenameFolder $command): void
    {
        $this->contentRepository->handle(SetNodeProperties::create(
            WorkspaceName::forLive(),
            NodeAggregateId::fromString($command->id->value),
            OriginDimensionSpacePoint::fromArray([]),
            PropertyValuesToWrite::fromArray([
                'label' => $command->newLabel,
            ]),
        ));
    }

    private function handleMoveFolder(MoveFolder $command): void
    {
        $newParentNodeAggregateId = $command->newParentFolderId === null ? self::assetsRootNodeId() : NodeAggregateId::fromString($command->newParentFolderId->value);
        $this->contentRepository->handle(MoveNodeAggregate::create(
            WorkspaceName::forLive(),
            DimensionSpacePoint::fromArray([]),
            NodeAggregateId::fromString($command->id->value),
            RelationDistributionStrategy::STRATEGY_GATHER_ALL,
            $newParentNodeAggregateId,
        ));
    }

    private function handleDeleteFolder(DeleteFolder $command): void
    {
        $this->contentRepository->handle(RemoveNodeAggregate::create(
            WorkspaceName::forLive(),
            NodeAggregateId::fromString($command->id->value),
            DimensionSpacePoint::fromArray([]),
            NodeVariantSelectionStrategy::STRATEGY_ALL_VARIANTS,
        ));
    }

    private function handleMoveAsset(MoveAsset $command): void
    {
        $newParentNodeAggregateId = $command->newParentFolderId === null ? self::assetsRootNodeId() : NodeAggregateId::fromString($command->newParentFolderId->value);
        $this->contentRepository->handle(MoveNodeAggregate::create(
            WorkspaceName::forLive(),
            DimensionSpacePoint::fromArray([]),
            NodeAggregateId::fromString($command->assetId->value),
            RelationDistributionStrategy::STRATEGY_GATHER_ALL,
            $newParentNodeAggregateId,
        ));
    }

    /** ######## HELPERS ######## */

    private static function tagsNodeId(): NodeAggregateId
    {
        return NodeAggregateId::fromString('tags');
    }

    private static function assetsRootNodeId(): NodeAggregateId
    {
        return NodeAggregateId::fromString('assets');
    }

    private function subgraph(): ContentSubgraphInterface
    {
        if ($this->contentSubgraphRuntimeCache === null) {
            $this->contentSubgraphRuntimeCache = $this->contentRepository->getContentGraph(WorkspaceName::forLive())->getSubgraph(DimensionSpacePoint::createWithoutDimensions(), VisibilityConstraints::withoutRestrictions());
        }
        return $this->contentSubgraphRuntimeCache;
    }
}
