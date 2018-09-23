<?php

namespace AppBundle\EventSubscriber;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class WebCronSubscriber implements EventSubscriberInterface {

  private $enabled;
  private $kernel;
  private $lockfile_path;

  public function __construct(KernelInterface $kernel, $enabled, $lockfile_path) {
    $this->enabled = $enabled;
    $this->kernel = $kernel;
    $this->lockfile_path = $lockfile_path;
  }

  public static function getSubscribedEvents() {
    // return the subscribed events, their methods and priorities
    return array(
       KernelEvents::TERMINATE => array(
         array('runCron', 10),
       ),
    );
  }

  public function runCron(PostResponseEvent $event) {
    //$request = $event->getRequest();
    //$reponse = $event->getResponse();

    // Halt if not enabled
    if (!$this->enabled) return;

    // Check last run time
    $last_run = (file_exists($this->lockfile_path) ? intval(trim(file_get_contents($this->lockfile_path))) : 0);

    // Run the cron command if it hasn't been run for over 1 hour
    if ((time() - $last_run) >= 3600) {
      $app = new Application($this->kernel);
      $app->setAutoExit(false);
      $input = new ArrayInput(array('command' => 'cron:run'));
      $output = new NullOutput();
      $app->run($input, $output);
      file_put_contents($this->lockfile_path, time());
    }
  }

}

// EOF
