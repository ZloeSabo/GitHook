#!/usr/bin/env php
<?php

use GitHooks\Application\HookApplication;
use GitHooks\Hook\HookInterface;

require __DIR__ . '/../../vendor/autoload.php';

$checkDirs = '@check.dirs@';

$application = new HookApplication();
$application->setRootDir(__DIR__ . '/../../');
$application->setCheckDirs($checkDirs);
$application->setHookType(HookInterface::TYPE_PRECOMMIT);
$application->run();
