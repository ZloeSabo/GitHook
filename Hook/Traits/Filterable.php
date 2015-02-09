<?php
/**
 * @author Evgeny Soynov <saboteur@saboteur.me>
 */

namespace GitHooks\Hook\Traits;


trait Filterable
{
    private function filter(array $files)
    {
        $extensions = $this->extensions;

        return array_filter($files, function($file) use ($extensions) {
            $fileInfo = new \SplFileInfo($file);

            return in_array($fileInfo->getExtension(), $extensions);
        });
    }

    //TODO is that ok to keep this in trait?
    public function run(array $files)
    {
        $phpScripts = $this->filter($files);

        return $this->runWithFiltered($phpScripts);
    }

    abstract function runWithFiltered(array $files);
} 