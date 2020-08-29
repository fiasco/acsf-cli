<?php

namespace SiteFactoryAPI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Table;
use SiteFactoryAPI\Config\ConfigFile;

class SiteInfo extends Command {
  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('site:info')
      ->setDescription('Get details on a specific site in Site Factory.')
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

    $client = ConfigFile::load($input->getArgument('sitegroup'))->getApiClient();

    $sitegroup = $input->getArgument('sitegroup');
    $siteID = $input->getArgument('siteid');

    $response = $client->request('GET', "sites/".$siteID, [
      'query' => [
      ]
    ]);
    $data = $response->getBody();
    $data = json_decode($data, TRUE);
    $list = $data;

    if (empty($list)) {
      return;
    }

    //print_r($data);

    $io->section($sitegroup." - ".$data["site"]."(".$siteID.")");

    //$ret = Array();
    //$i = -1;
    $table = new Table($output);
    $table->setStyle("compact");
    //$table->setHeaders([$data["site"]."(".$siteID.")"],['colspan'=>2]);
    foreach ($data as $key => $val) {
      if (!is_array($val)) {
        if ($key=="created") {
          $val = date('m/d/Y H:i:s e',$val);
        }
        $table->addRow([$key,$val]);
      }
    }
    $table->render();

    $arg = ['sitegroup'=>$sitegroup,'siteid'=>$siteID];
    $domainInput = new ArrayInput($arg);
    $command = $this->getApplication()->find('site:domains');
    $retcode = $command->run($domainInput,$output);

  }
}

 ?>
