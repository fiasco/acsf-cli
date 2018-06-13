<?php

namespace SiteFactoryAPI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use SiteFactoryAPI\Config\ConfigFile;

class CreateSite extends Command {
  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('site:create')
      ->setDescription('Create a site')
      ->addArgument(
        'sitegroup',
        InputArgument::REQUIRED,
        'Combination of sitename and environment in one word. E.g. mystack01live.'
      )
      ->addArgument(
        'site_name',
        InputArgument::REQUIRED,
        'The new site name.'
      )
      ->addOption(
        'group_id',
        'g',
        InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
        'Either a single group ID, or an array of group IDs.'
      )
      ->addOption(
        'install_profile',
        'p',
        InputOption::VALUE_OPTIONAL,
        'The install profile to be used to install the site.'
      );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);

    $data = array_filter([
      'site_name' => $input->getArgument('site_name'),
      'group_ids' => array_map(function ($id) {
        return (int) $id; },
        $input->getOption('group_id')),
      'install_profile' => $input->getOption('install_profile'),
      'codebase' => 1,
    ]);

    $client = ConfigFile::load($input->getArgument('sitegroup'))->getApiClient();

    $response = $client->request('POST', "sites", [
      'json' => $data
    ]);
    $data = $response->getBody();
    $data = json_decode($data, TRUE);

    $io->success("Task created: {$data['site']} ({$data['id']}) - {$data['domains'][0]}");
  }
}

 ?>
