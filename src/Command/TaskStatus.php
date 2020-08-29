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

class TaskStatus extends Command {
  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('task:status')
      ->setDescription('Returns log entries about WIP tasks..')
      ->addArgument(
        'sitegroup',
        InputArgument::REQUIRED,
        'Combination of sitename and environment in one word. E.g. mystack01live.'
      )
      ->addArgument(
        'taskid',
        InputArgument::REQUIRED,
        'Task ID for the WIP being queried.'
      );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);

    $client = ConfigFile::load($input->getArgument('sitegroup'))->getApiClient();
    $sitegroup = $input->getArgument('sitegroup');
    $taskid = $input->getArgument('taskid');

    $response = $client->request('GET', "wip/task/$taskid/status", [
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

    $io->section($sitegroup." - Task: $taskid");

    $table = new Table($output);
    $table->setStyle("compact");
    //$table->setHeaders([$data["site"]."(".$siteID.")"],['colspan'=>2]);
    foreach ($data["wip_task"] as $key => $val) {
      if (!is_array($val)) {
        if ($key=="added" || $key=="started" || $key=="completed") {
          $val = date('m/d/Y H:i:s e',$val);
        }
        $table->addRow([$key,$val]);
      }
    }
    $table->render();

    //$ret = Array();
    //$i = -1;
    //foreach ($data as $key => $val) {
    //   $i += 1;
    //   $ret[$i] = "$key: $val";
    //}
    //$io->section($input->getArgument('sitegroup'));
    //$io->text($ret);

    $io->newline();
  }
}

 ?>
