<?php
/**
 * @author Evgeny Soynov <saboteur@saboteur.me>
 */

namespace GitHooks\Application;


use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;


use GitHooks\Hook\Loader;
use GitHooks\Exception\HookException;


class HookApplication extends Application
{
    private $projectRoot;
    private $checkDirs;
    private $loader;
    private $hookType;

    public function __construct()
    {
        $this->setupLoader();

        parent::__construct();
    }

    public function setRootDir($dir)
    {
        $this->projectRoot = $dir;
    }

    public function setCheckDirs($checkDirs)
    {
        $this->checkDirs = $checkDirs;
    }

    public function setHookType($hookType)
    {
        $this->hookType = $hookType;
    }

    private function setupLoader()
    {
        $this->loader = new Loader();
    }

    private function getChangedFiles()
    {
        $builder = new ProcessBuilder(['git', 'diff', '--cached', '--name-status', '--diff-filter=ACMR']);
        $builder->setWorkingDirectory($this->projectRoot);

        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'Cant get list of changed files: %s',
                $process->getErrorOutput()
            ));
        }

        return $process->getOutput();
    }

    //TODO separate parser
    private function parseChangedFiles($output)
    {
        $files = [];

        foreach(preg_split("/((\r?\n)|(\r\n?))/", $output) as $line) {
            $line = trim($line);
            if(empty($line)) {
                continue;
            }
            $parts = preg_split('/[\s]+/', $line);
            $filename = array_pop($parts);

            if(file_exists($filename)) { //DO I seriously need it?
                $files[] = $filename;
            }
        }

        return $files;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        try {
            $diffOutput = $this->getChangedFiles();
            $files = $this->parseChangedFiles($diffOutput);

            //TODO load this from config
            $hooks = $this->loader->load($this->checkDirs, $this->hookType);

            foreach($hooks as $hook) {
                $hook->run($files);
            }
        } catch (\RuntimeException $e) {
            $output->writeln(sprintf(
                '<error>[Git Hook] error: %s</error>',
                $e->getMessage()
            ));

            return 1;
        } catch (HookException $e) {
            $output->writeln(sprintf(
                '<error>[Git Hook] %s:</error>%s<info>%s</info>',
                $e->getMessage(),
                PHP_EOL,
                $e->getCheckOutput()
            ));

            return 1;
        }

        return 0;
    }
}
