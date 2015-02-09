<?php
/**
 * @author Evgeny Soynov <saboteur@saboteur.me>
 */

namespace GitHooks;

use Composer\Composer;
use Composer\IO\IOInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Installer
{
    /** @var Composer */
    private $composer;

    /** @var IOInterface */
    private $io;

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function install()
    {
        $root = $this->identifyProjectRoot($this->composer);

        $gitDir = $root . DIRECTORY_SEPARATOR . '.git';
        $hookDir =  $gitDir. DIRECTORY_SEPARATOR . 'hooks';
        if(!file_exists($gitDir)) {
            $this->io->write('<error>.git directory does not exist. Skipping hook installation</error>');
            return;
        }

        if(!$this->isInstalled($hookDir)) {

            $this->io->write('Installing git hooks...', false);
            try {
                $checkLocations = $this->getCheckLocations($this->composer);
                $this->installHooks($hookDir, $checkLocations);
                $this->io->write(' done');
            } catch (IOExceptionInterface $e) {
                $this->io->write('<error>failed</error>');
                $this->io->write(sprintf(
                    '<error>Error: %s</error>',
                    $e->getMessage()
                ));
            }
        }
    }

    private function identifyProjectRoot(Composer $composer)
    {
        $config = $composer->getConfig();

        //TODO avoid use of reflection magic
        $method = new \ReflectionMethod(get_class($config), 'realpath');
        $method->setAccessible(true);
        $root = $method->invokeArgs($config, ['']);

        return $root;
    }

    private function isInstalled($hookDir)
    {
//        return false;
//
        $finder = new Finder();
        $iterator = $finder
            ->files()
            ->name('pre-commit')
            ->depth(0)
            ->in($hookDir)
            ->contains('HookApplication')
        ;

        return $iterator->count() > 0;
    }

    private function getCheckLocations(Composer $composer)
    {
        $extra = $composer->getPackage()->getExtra();
        if(empty($extra) || empty($extra['git-hooks'])) {
            //TODO relative path
            return [__DIR__ . '/Hook'];
        }

        return array_reduce($extra['git-hooks'], function($everything, $hookLocation) {
            if($hookLocation == '@default') {
                $everything[] = __DIR__ . '/Hook';
            } elseif (file_exists($hookLocation)) {
                $everything[] = $hookLocation;
            }

            return $everything;
        }, []);
    }


    private function stringifyArray($locations)
    {
        $stringified = array_reduce($locations, function($everything, $location) {
            return $everything . sprintf("    '%s',%s", $location, PHP_EOL);
        }, "");

        return sprintf(
            "[%s%s%s]",
            PHP_EOL,
            $stringified,
            PHP_EOL
        );
    }

    private function installHooks($hookDir, $checkLocations)
    {
        $checkLocationsString = $this->stringifyArray($checkLocations);
        //TODO check hook versions
        $fs = new Filesystem();
        $source = __DIR__ . DIRECTORY_SEPARATOR . 'ScriptTemplate' . DIRECTORY_SEPARATOR . 'PreCommit.php';
        $hookContents = file_get_contents($source);
        $hookContents = str_replace("'@check.dirs@'", $checkLocationsString, $hookContents);


        $target = $hookDir . DIRECTORY_SEPARATOR . 'pre-commit';

        $fs->dumpFile($target, $hookContents);
        $fs->chmod($target, 0700, 0000);
    }

}