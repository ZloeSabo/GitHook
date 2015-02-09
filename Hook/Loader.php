<?php
/**
* @author Evgeny Soynov <saboteur@saboteur.me>
*/

namespace GitHooks\Hook;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Loader
{
    public function load(array $directories, $hookType)
    {
        $checkFiles = $this->findFilesForLoading($directories);

        //TODO may be load just before check?
        return $this->loadChecks($checkFiles, $hookType);
    }

    private function findFilesForLoading(array $directories)
    {
        $finder = new Finder();
        $checkFiles = $finder
            ->files()
            ->in($directories)
            ->ignoreVCS(true)
            ->depth(0)
            ->name('*Hook.php')
            ;

        return $checkFiles;
    }

    private function loadChecks(Finder $files, $hookType)
    {
        $hooks = [];

        foreach($files as $file) {
            $className = $this->parseClassFromFile($file);
            if(empty($className) || !class_exists($className)) {
                continue;
            }

            $reflected = new \ReflectionClass($className);

            if(!$reflected->isAbstract() && $reflected->implementsInterface('GitHooks\Hook\HookInterface')) {
                $hook = $reflected->newInstance();

                //TODO dont like such way
                if($hook->type() === $hookType) {
                    $hooks[] = $hook;
                }
            }
        }

        return $hooks;
    }

    private function parseClassFromFile(SplFileInfo $file)
    {
        //TODO must be some better way
        $contents = $file->getContents();
        preg_match_all('/namespace ([^;]*);/i', $contents, $matches);

        if(count($matches) == 2 && !empty($matches[1])) {
            $fqcn = sprintf(
                '%s\%s',
                $matches[1][0],
                $file->getBasename('.' . $file->getExtension())
            );

            return $fqcn;
        }

        return null;
    }
}
