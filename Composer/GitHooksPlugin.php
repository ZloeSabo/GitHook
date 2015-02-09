<?php
/**
 * @author Evgeny Soynov <saboteur@saboteur.me>
 */

namespace GitHooks\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Script\CommandEvent;
use Composer\EventDispatcher\EventSubscriberInterface;


use GitHooks\Installer;

class GitHooksPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        //Keep this to make Composer happy
        //$this->installHooks($composer, $io);
    }

    private function installHooks(Composer $composer, IOInterface $io)
    {
        $installer = new Installer($composer, $io);
        $installer->install();
    }

    public function activateWithEvent(CommandEvent $event)
    {
        $composer = $event->getComposer();
        $io = $event->getIO();

        $this->installHooks($composer, $io);
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_AUTOLOAD_DUMP => 'activateWithEvent'
        ];
    }
} 