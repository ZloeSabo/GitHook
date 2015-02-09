<?php
/**
 * @author Evgeny Soynov <saboteur@saboteur.me>
 */

namespace GitHooks\Hook;

use GitHooks\Hook\Traits\Filterable;


abstract class BaseHook
{
    use Filterable;

    protected $extensions = ['php', 'inc']; //Cant be defined inside trait
}