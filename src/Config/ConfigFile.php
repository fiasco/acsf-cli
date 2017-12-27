<?php

namespace SiteFactoryAPI\Config;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use GuzzleHttp\Client;

class ConfigFile {
  protected $credentials = [];

  protected $endpoint;

  public function __construct($filename)
  {
    $value = Yaml::parseFile($filename);

    $this->credentials['username'] = $value['credentials']['username'];
    $this->credentials['key'] = $value['credentials']['key'];

    $this->endpoint = $value['endpoint'];
  }

  protected function getAuthCredentials()
  {
    return [
       $this->credentials['username'],
       $this->credentials['key']
    ];
  }

  protected function getEndpoint()
  {
    return $this->endpoint;
  }

  public function getApiClient()
  {
    return new Client([
        //'base_uri' => 'https://www.govcms.acsitefactory.com/api/v1/',
        'base_uri' => $this->getEndpoint(),
        'auth' => $this->getAuthCredentials(),
    ]);
  }

  static public function load($sitegroup)
  {
    $finder = new Finder();
    $finder->files()
      ->in([getcwd(), $_SERVER['HOME']])
      ->depth('== 0')
      ->name($sitegroup . '.conf.yml');

    if (!$finder->count()) {
      throw new \Exception("Could not find config file: $sitegroup.conf.yaml");
    }
    foreach ($finder as $file) {
      return new static($file->getRealPath());
    }
  }
}

 ?>
