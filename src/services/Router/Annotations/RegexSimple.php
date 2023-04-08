<?php

namespace Api\Router\Annotations;

enum RegexSimple: string
{
    case int = '[0-9]+';
    case string = '[a-zA-Z]+';
    case alphanum = '[a-zA-Z0-9]+';
    case bool = '(true|false)';
    case float = '[0-9]+.[0-9]+';
}
