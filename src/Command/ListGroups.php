<?php

namespace SiteFactoryAPI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use SiteFactoryAPI\Config\ConfigFile;

class ListGroups extends Command {
  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('group:list')
      ->setDescription('Get a list of groups.')
      ->addArgument(
        'sitegroup',
        InputArgument::REQUIRED,
        'Combination of sitename and environment in one word. E.g. mystack01live.'
      );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);

    $client = ConfigFile::load($input->getArgument('sitegroup'))->getApiClient();

    $response = $client->request('GET', "groups");
    $data = $response->getBody();
    $data = json_decode($data, TRUE);
    $list = $data['groups'];

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
