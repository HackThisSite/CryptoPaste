<?php

namespace AppBundle\Command;

//use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use AppBundle\Model\PasteModel;
use AppBundle\Model\SessionModel;

class CronRunCommand extends ContainerAwareCommand {

  use LockableTrait;

  private $log;
  private $pastes;
  private $sessions;

  public function __construct(LoggerInterface $log, PasteModel $pastes, SessionModel $sessions) {
    $this->log = $log;
    $this->pastes = $pastes;
    $this->sessions = $sessions;
    parent::__construct();
  }

  protected function configure() {
    $this
      ->setName('cron:run')
      ->setDescription('Run cron command. Deletes expired pastes and sessions.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    // Check and write lock
    if (!$this->lock()) {
      $this->log->error('CRON: Already running in another process');
      return 1;
    }
    $this->log->debug('CRON: Running cron command');

    // Set variables
    $exit = 0;
    $db = $this->doctrine->getManager();

    // Flush expired pastes
    $this->log->debug('CRON: Flushing expired pastes');
    $pastes_flushed = $pastes->deleteExpired();
    $this->log->info(sprintf('CRON: Flushed %d expired paste%s', count($pastes_flushed), (count($pastes_flushed) == 1 ? '' : 's')));

    // Flush expired sessions
    $this->log->debug('CRON: Flushing expired sessions');
    $sessions_flushed = $sessions->deleteExpired();
    $this->log->info(sprintf('CRON: Flushed %d expired session%s', count($sessions_flushed), (count($sessions_flushed) == 1 ? '' : 's')));

    // Release lock
    $this->release();
    $this->log->debug('CRON: Command completed');
    return $exit;
  }

}

// EOF
