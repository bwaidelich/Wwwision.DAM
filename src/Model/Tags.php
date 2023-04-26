<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<Tag>
 */
final class Tags implements IteratorAggregate, Countable
{

    /**
     * @param Tag[] $tags
     */
    private function __construct(
        private readonly array $tags,
    ) {}

    public static function create(Tag ...$tags): self
    {
        return new self($tags);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->tags);
    }

    public function count(): int
    {
        return count($this->tags);
    }
}
