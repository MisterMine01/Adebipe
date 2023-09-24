<?php

namespace Adebipe\Router\Annotations;

use Adebipe\Builder\NoBuildable;

/**
 * Regex for routes (for simplify the use of regex)
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
#[NoBuildable]
enum RegexSimple: string
{
    case int = '[0-9]+';
    case string = '[a-zA-Z]+';
    case alphanum = '[a-zA-Z0-9]+';
    case bool = '(true|false)';
    case float = '[0-9]+.[0-9]+';
}
