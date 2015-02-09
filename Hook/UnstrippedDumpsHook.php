<?php
/**
 * @author Evgeny Soynov <saboteur@saboteur.me>
 */

namespace GitHooks\Hook;


use GitHooks\Exception\HookException;

class UnstrippedDumpsHook extends BaseHook implements HookInterface
{
    public function runWithFiltered(array $files)
    {
        foreach($files as $script) {
            $fileContents = file_get_contents($script);
            $fileContents = strtolower($fileContents);
            $fileContents = $this->replaceSafeDumps($fileContents);

            switch(true) {
                case $this->hasVarDumps($fileContents):
                    throw new HookException(sprintf('Check failed [%s]', $script), 'Please remove var_dump');
                    break;
                case $this->hasPrintR($fileContents):
                    throw new HookException(sprintf('Check failed [%s]', $script), 'Please remove print_r');
                    break;
                default:
                    break;
            }
        }
    }

    public function type()
    {
        return HookInterface::TYPE_PRECOMMIT;
    }

    private function replaceSafeDumps($fileContents)
    {
        //print_r(..., true) can be used for logging purposes
        return preg_replace('/print_r\(.+(?:,\s*true\))/', '', $fileContents);
    }

    private function hasVarDumps($fileContents)
    {
        return strpos($fileContents, 'var_dump(') !== false;
    }

    private function hasPrintR($fileContents)
    {
        return strpos($fileContents, 'print_r(') !== false;
    }
} 