<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * A (possible empty) set of assets
 * @implements IteratorAggregate<Asset>
 */
final class Assets implements IteratorAggregate, Countable
{

    /**
     * @param array<Asset> $assets
     */
    private function __construct(
        private readonly array $assets,
    ) {}

    public static function create(Asset ...$assets): self
    {
        return new self($assets);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->assets);
    }

    public function count(): int
    {
        return count($this->assets);
    }
}
