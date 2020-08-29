<?php

namespace SiteFactoryAPI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use SiteFactoryAPI\Config\ConfigFile;

class SiteCache extends Command {
  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('site:cache')
      ->setDescription('Clears caches for a given site id.')
      ->addArgument(
        'sitegroup',
        InputArgument::REQUIRED,
        'Combination of sitename and environment in one word. E.g. mystack01live.'
      )
      ->addArgument(
        'siteid',
        InputArgument::REQUIRED,
        'The site id for the site.'
      );
}

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);

    $sitegroup = $input->getArgument('sitegroup');
    $siteID = $input->getArgument('siteid');

    $client = ConfigFile::load($input->getArgument('sitegroup'))->getApiClient();

    $response = $client->request('POST', "sites/".$siteID."/cache-clear", [
      'query' => [
      ]
    ]);
    $data = $response->getBody();
    $data = json_decode($data, TRUE);
    $list = $data;

    if (empty($list)) {
      return;
    }

    print_r($data);

    //$ret = Array();
    //$i = -1;
    //foreach ($data as $key => $val) {
    //   $i += 1;
    //   $ret[$i] = "$key: $val";
    //}
    //$io->section($input->getArgument('sitegroup'));
    //$io->text($factory);
    //$io->newline();
  }
}

 ?>
