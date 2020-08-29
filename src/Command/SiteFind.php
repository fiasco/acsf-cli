<?php

namespace SiteFactoryAPI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Table;
use SiteFactoryAPI\Config\ConfigFile;

class SiteFind extends Command {
  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('site:find')
      ->setDescription('Searches for a site name containing the search needle.')
      ->addArgument(
        'sitegroup',
        InputArgument::REQUIRED,
        'Combination of sitename and environment in one word. E.g. mystack01live.'
      )
      ->addOption(
        'search',
        's',
        InputOption::VALUE_OPTIONAL,
        'Site name search string.'
      )
      ->addOption(
        'csv',
        null,
        InputOption::VALUE_NONE,
        'Output as comma separated values.'
      )
      ->addOption(
        'all',
        'a',
        InputOption::VALUE_OPTIONAL,
        'Returns all sites, disregards the search string.',
        false
      );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);

    $client = ConfigFile::load($input->getArgument('sitegroup'))->getApiClient();
    //$factory = ConfigFile::load($input->getArgument('sitegroup'))->getFactory();

    $page = 0;
    $search = $input->getOption('search');
    $all = $input->getOption('all');
    $optionCSV = $input->getOption('csv');

    $i = -1;
    $ret = array();
    do {
      $page += 1;
      $response = $client->request('GET', "sites", [
        'query' => [
          'limit' => '100',
          'page' => $page
        ]
      ]);
      //$io->text('Page: '.$page);
      $data = $response->getBody();
      $data = json_decode($data, TRUE);
      if ($page == 1) {
        $siteCount = $data["count"];
        //print_r($data);
      }
      //$io->text('Records: '.count($data["sites"]));
      //apply search pattern
      foreach ($data["sites"] as $key => $val) {
        //check the pattern
        if (false === $all) {
          $matches = array();
          preg_match('/'.$search.'/',$val["site"],$matches);
          if (count($matches) > 0) {
            $i += 1;
            //$ret[$i] = "$key: ".$val["site"];
            $ret[$i] = array($val["id"],$val["site"],$val["db_name"],$val["stack_id"],);
          }
        } else {
          $i += 1;
          //$ret[$i] = "$key: ".$val["site"];
          $ret[$i] = array($val["id"],$val["site"],$val["db_name"],$val["stack_id"],);
        }
      }
    } while (count($data["sites"])>0);

    //print_r($ret);

    if ($optionCSV === false) {
      $io->title($input->getArgument('sitegroup')." - Sites: ".count($ret));
      $table = new Table($output);
      $table->setHeaders(['ID','Name','Database','Stack']);
      $table->setRows($ret);
      $table->render();
    } else {
      // CSV output
      $filename = $input->getArgument('sitegroup').'-sites.csv';
      $file = fopen($filename,"w");
      fputcsv($file,['ID','Name','Database','Stack']);
      foreach ($ret as $k => $v) {
        fputcsv($file,$v);
      }
      fclose($file);
      $io->writeln('file written');
    }

    //$io->text('Hello World!');
    //$io->newline();

  }
}

 ?>
