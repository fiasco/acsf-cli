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

class TrafficMonthly extends Command {
  /**
   * @inheritdoc
   */
  protected function configure() {
    date_default_timezone_set('UTC');

    $this
      ->setName('traffic:monthly')
      ->setDescription('Gets the monthly aggregated dynamic request statistics..')
      ->addArgument(
        'sitegroup',
        InputArgument::REQUIRED,
        'Combination of sitename and environment in one word. E.g. mystack01live.'
      )
      ->addOption(
        'asc',
        null,
        InputOption::VALUE_NONE,
        'Ascending sort order. Descending is the default.'
      )
      ->addOption(
        'csv',
        null,
        InputOption::VALUE_NONE,
        'Output as comma separated values.'
      )
      ->addOption(
        'start',
        's',
        InputOption::VALUE_OPTIONAL,
        'Start date for the query YYYY-MM.',
        date("Y-m")
      )
      ->addOption(
        'limit',
        'l',
        InputOption::VALUE_OPTIONAL,
        'A positive integer (max 100).',
        12
      );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);

    $client = ConfigFile::load($input->getArgument('sitegroup'))->getApiClient();

    //load some options
    $optionSort = $input->getOption('asc');
    $optionCSV = $input->getOption('csv');

    if ($optionSort === false) {
      $sort = 'desc';
    } else {
      $sort = 'asc';
    }

    $response = $client->request('GET', "dynamic-requests/monthly", [
      'query' => [ 'start_from' => $input->getOption('start'),
      'limit' => $input->getOption('limit'),
      'sort_order' => $sort,
      'stack_id' => '1'
      ]
    ]);
    $data = $response->getBody();
    $data = json_decode($data, TRUE);
    $list = $data;

    if (empty($list)) {
      return;
    }

    //print_r($data);

    $result = Array();
    foreach($data["dynamic_requests"] as $key => $value) {
       $result[$key] = array($value["date"],$value["total_dynamic_requests"],
                       $value["2xx_dynamic_requests"],$value["3xx_dynamic_requests"],
                       $value["4xx_dynamic_requests"],$value["5xx_dynamic_requests"]
                       );
    };

    //print_r($result);

    //pick output method, normal or csv
    if ($optionCSV === false) {
      $io->title($input->getArgument('sitegroup').' - '.$input->getOption('start'));
      $table = new Table($output);
      $table->setHeaders(['Date','Total','2xx','3xx','4xx','5xx']);
      $table->setRows($result);
      $table->render();
    } else {
      // CSV output
      $filename = $input->getArgument('sitegroup').'-'.$input->getOption('start').'.csv';
      $file = fopen($filename,"w");
      fputcsv($file,['Date','Total','2xx','3xx','4xx','5xx']);
      foreach ($result as $k => $v) {
        fputcsv($file,$v);
      }
      fclose($file);
      $io->writeln('file written');
    }

    //$ret = Array();
    //$i = -1;
    //foreach ($data as $key => $val) {
    //   $i += 1;
    //   $ret[$i] = "$key: $val";
    //}
    //
    //$io->text($ret);
    //$io->newline();
  }
}

 ?>
