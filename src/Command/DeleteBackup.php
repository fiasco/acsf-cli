<?php

namespace SiteFactoryAPI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use SiteFactoryAPI\Config\ConfigFile;

class DeleteBackup extends Command {
  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('backups:delete')
      ->setDescription('Delete a backup')
      ->addArgument(
        'sitegroup',
        InputArgument::REQUIRED,
        'Combination of sitename and environment in one word. E.g. mystack01live.'
      )
      ->addArgument(
        'site_id',
        InputArgument::REQUIRED,
        'Site ID'
      )
      ->addArgument(
        'backup_id',
        InputArgument::REQUIRED,
        'Backup ID - (See backups:list)'
      );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);
    $site_id = $input->getArgument('site_id');
    $backup_id = $input->getArgument('backup_id');

    $client = ConfigFile::load($input->getArgument('sitegroup'))->getApiClient();

    $response = $client->request('DELETE', "sites/$site_id/backups/$backup_id", [
      'headers' => [
        'Content-Type' => 'application/json'
      ]
    ]);
    $data = $response->getBody();
    $data = json_decode($data, TRUE);

    $io->success("Task created: {$data['task_id']}");
  }
}

 ?>
