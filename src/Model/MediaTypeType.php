<?php
declare(strict_types=1);
namespace Wwwision\DAM\Model;

/**
 * The "type" part of a Media Type (@see https://en.wikipedia.org/wiki/Media_type#Types)
 */
enum MediaTypeType : string
{
    case application = 'application';
    case audio = 'audio';
    case image = 'image';
    case message = 'message';
    case multipart = 'multipart';
    case text = 'text';
    case video = 'video';
    case font = 'font';
    case example = 'example';
    case model = 'model';
}
