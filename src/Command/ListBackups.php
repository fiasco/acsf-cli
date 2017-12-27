<?php

namespace SiteFactoryAPI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use SiteFactoryAPI\Config\ConfigFile;

class ListBackups extends Command {
  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('backups:list')
      ->setDescription('List site backups.')
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
      ->addOption(
        'limit',
        'l',
        InputOption::VALUE_OPTIONAL,
        'A positive integer (max 100).',
        10
      )
      ->addOption(
        'page',
        'p',
        InputOption::VALUE_OPTIONAL,
        'A positive integer.',
        1
      );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);
    $site_id = $input->getArgument('site_id');

    $client = ConfigFile::load($input->getArgument('sitegroup'))->getApiClient();

    try {
      $response = $client->request('GET', "sites/$site_id/backups", [
        'query' => [
          'page' => $input->getOption('page'),
          'limit' => $input->getOption('limit')
        ]
      ]);

      $data = $response->getBody();
    }
    catch (\Exception $e) {
      $io->error($e->getMessage());
      return;
    }
    $data = $response->getBody();
    $data = json_decode($data, TRUE);
    $list = $data['backups'];

    foreach ($list as &$backup) {
      $backup['componentList'] = implode(', ', $backup['componentList']);
      $backup['date'] = date('Y-m-d H:i:s', $backup['timestamp']);
    }
    $io->table(array_keys($list[0]), $list);
  }
}

 ?>
