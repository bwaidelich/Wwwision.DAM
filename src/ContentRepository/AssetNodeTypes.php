<?php
declare(strict_types=1);
namespace Wwwision\DAM\ContentRepository;

use Neos\ContentRepository\Core\NodeType\NodeType;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Wwwision\DAM\Model\AssetCaption;
use Wwwision\DAM\Model\AssetLabel;
use Wwwision\DAM\Model\Filename;
use Wwwision\DAM\Model\FolderLabel;
use Wwwision\DAM\Model\MediaType;
use Wwwision\DAM\Model\Metadata;
use Wwwision\DAM\Model\ResourcePointer;
use Wwwision\DAM\Model\Dimensions;
use Wwwision\DAM\Model\TagLabel;

enum AssetNodeTypes
{
    case Assets; // root node containing assets and folders

    case Folder; // a single folder (aka AssetCollection)

    case Asset; // abstract super type for all assets

    case Image;
    case Document;
    case Video;
    case Audio;

    case Tags; // node containing tags
    case Tag; // a single tag

    public function name(): NodeTypeName
    {
        return NodeTypeName::fromString('Wwwision.DAM:' . $this->name);
    }

    public static function isAssetNodeType(NodeTypeName $candidate): bool
    {
        return in_array($candidate->value, array_map(fn (self $type) => $type->name, [self::Image, self::Document, self::Video, self::Audio]), true);
    }

    public static function getConfiguration(): array
    {
        return [
            'Neos.ContentRepository:Root' => [
                'abstract' => true,
            ],
            self::Assets->name()->value => [
                'label' => 'Assets Root',
                'superTypes' => [
                    'Neos.ContentRepository:Root' => true,
                ]
            ],
            self::Folder->name()->value => [
                'label' => 'Folder',
                'properties' => [
                    'label' => [
                        'type' => FolderLabel::class,
                    ],
                ],
            ],
            self::Asset->name()->value => [
                'label' => 'Asset',
                'abstract' => true,
                'properties' => [
                    'resourcePointer' => [
                        'type' => ResourcePointer::class,
                    ],
                    'mediaType' => [
                        'type' => MediaType::class,
                    ],
                    'filename' => [
                        'type' => Filename::class,
                    ],
                    'metadata' => [
                        'type' => Metadata::class,
                    ],
                    'label' => [
                        'type' => AssetLabel::class,
                    ],
                    'caption' => [
                        'type' => AssetCaption::class,
                    ],
                    'tags' => [
                        'type' => 'references'
                    ],
                ],
            ],
            self::Audio->name()->value => [
                'label' => 'Audio',
                'superTypes' => [
                    self::Asset->name()->value => true,
                ],
            ],
            self::Document->name()->value => [
                'label' => 'Document',
                'superTypes' => [
                    self::Asset->name()->value => true,
                ],
            ],
            self::Image->name()->value => [
                'label' => 'Image',
                'superTypes' => [
                    self::Asset->name()->value => true,
                ],
                'properties' => [
                    'dimensions' => [
                        'type' => Dimensions::class,
                    ],
                ],
            ],
            self::Video->name()->value => [
                'label' => 'Video',
                'superTypes' => [
                    self::Asset->name()->value => true,
                ],
                'properties' => [
                    'dimensions' => [
                        'type' => Dimensions::class,
                    ],
                ],
            ],

            self::Tags->name()->value => [
                'label' => 'Tags',
                'superTypes' => [
                    'Neos.ContentRepository:Root' => true,
                ]
            ],
            self::Tag->name()->value => [
                'label' => 'Tag',
                'properties' => [
                    'label' => [
                        'type' => TagLabel::class,
                    ],
                ],
            ],
        ];
    }
}
