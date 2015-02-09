<?php

/**
* @author Evgeny Soynov <saboteur@saboteur.me>
*/

namespace GitHooks\Hook;

use Symfony\Component\Process\ProcessBuilder;
use GitHooks\Exception\HookException;

class PhpLintHook extends BaseHook implements HookInterface
{
    public function runWithFiltered(array $files = [])
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix('php');


        foreach($files as $file)
        {
            $process = $builder->setArguments(['-l', $file])->getProcess();
            $process->run();

            if(!$process->isSuccessful()) {
                throw new HookException(
                    sprintf('Lint check failed [%s]', $file),
                    $process->getErrorOutput()
                );
            }
        }
    }

    public function type()
    {
        return HookInterface::TYPE_PRECOMMIT;
    }
}
