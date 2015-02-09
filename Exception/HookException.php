<?php
/**
* @author Evgeny Soynov <saboteur@saboteur.me>
*/

namespace GitHooks\Exception;

class HookException extends \Exception
{
    protected $checkOutput;

    public function __construct($message, $checkOutput = '')
    {
        $this->checkOutput = $checkOutput;
        parent::__construct($message);
    }

    public function getCheckOutput()
    {
        return $this->checkOutput;
    }

    public function setCheckOutput($output)
    {
        $this->checkOutput = $output;
    }
}
