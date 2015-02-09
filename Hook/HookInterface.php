<?php
/**
 * @author Evgeny Soynov <saboteur@saboteur.me>
 */

namespace GitHooks\Hook;


interface HookInterface
{
    const TYPE_PRECOMMIT = 'pre-commit';

    /**
     * Used to run this hook
     * @param array $files list of changed files
     * @return null
     */
    public function run(array $files);

    /**
     * Used to filter hook types
     * @return string
     */
    public function type();
}
