<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model\Filter;

enum OrderingField: string
{
    case LAST_MODIFIED = 'LAST_MODIFIED';
    case NAME = 'NAME';
}
