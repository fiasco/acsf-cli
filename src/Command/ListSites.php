<?php

namespace SiteFactoryAPI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use SiteFactoryAPI\Config\ConfigFile;

class ListSites extends Command {
  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('sites:list')
      ->setDescription('List site backups.')
      ->addArgument(
        'sitegroup',
        InputArgument::REQUIRED,
        'Combination of sitename and environment in one word. E.g. mystack01live.'
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
      )
      ->addOption(
        'canary',
        'c',
        InputOption::VALUE_NONE,
        'A positive integer.'
      );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);

    $client = ConfigFile::load($input->getArgument('sitegroup'))->getApiClient();

    $response = $client->request('GET', "sites", [
      'query' => [
        'page' => $input->getOption('page'),
        'limit' => $input->getOption('limit')
      ]
    ]);
    $data = $response->getBody();
    $data = json_decode($data, TRUE);
    $list = $data['sites'];

    if (empty($list)) {
      return;
    }

    foreach ($list as &$site) {
      $site['groups'] = isset($site['groups']) ? implode(', ', $site['groups']) : '';
    }
    $io->table(array_keys($list[0]), $list);
  }
}

 ?>
