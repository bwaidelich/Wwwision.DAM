<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * a (possible empty) set of folders
 * @implements IteratorAggregate<Folder>
 */
final class Folders implements IteratorAggregate, Countable
{

    /**
     * @param Folder[] $folders
     */
    private function __construct(
        private readonly array $folders,
    ) {}

    public static function create(Folder ...$folders): self
    {
        return new self($folders);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->folders);
    }

    public function count(): int
    {
        return count($this->folders);
    }
}
