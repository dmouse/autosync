<?php

/**
 * @file
 * Contains Drupal\autosync\Controller\ConfigController.
 */

namespace Drupal\autosync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Guzzle\Http\Client;
use GuzzleHttp\Post\PostFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigManager;
use Drupal\Core\Config\CachedStorage;
use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Component\Serialization\Yaml;

/**
 * Class ConfigController.
 *
 * @package \Drupal\autosync\Controller
 */
class ConfigController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Drupal\Core\Config\ConfigManager definition.
   *
   * @var \Drupal\Core\Config\ConfigManager
   */
  protected $config_manager;

  /**
   * Drupal\Core\Config\CachedStorage definition.
   *
   * @var \Drupal\Core\Config\CachedStorage
   */
  protected $config_storage;
  public function __construct(ConfigManager $config_manager, CachedStorage $config_storage) {
    $this->configManager = $config_manager;
    $this->targetStorage = $config_storage;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.manager'),
      $container->get('config.storage')
    );
  }

  /**
   * Generatetar.
   *
   * @return string
   *   Return Hello string.
   */
  public function generateTar() {

    file_unmanaged_delete(file_directory_temp() . '/config-autosync.tar.gz');

    $archiver = new ArchiveTar(file_directory_temp() . '/config-autosync.tar.gz', 'gz');

    foreach ($this->configManager->getConfigFactory()->listAll() as $name) {
      $config = $this->configManager
        ->getConfigFactory()
        ->get($name)
        ->getRawData();
      $archiver->addString("$name.yml", Yaml::encode($config));
    }


    foreach ($this->targetStorage->getAllCollectionNames() as $collection) {
      $collection_storage = $this->targetStorage
        ->createCollection($collection);

      foreach ($collection_storage->listAll() as $name) {
        $collection = Yaml::encode($collection_storage->read($name));
        $archiver->addString(
          str_replace('.', '/', $collection) . "/$name.yml",
          $collection
        );
      }
    }

    $key = "2aa523ce00648012c2a7afcd3da452109d470a34";
    $client = new \GuzzleHttp\Client();
    $response = $client->post("http://configsets.dev/app_dev.php/save-config?key=$key", [
      'body' => [
        "file" => new PostFile('config-file',
          fopen(file_directory_temp() . '/config-autosync.tar.gz', 'r')
        )
      ]
    ]);

    return [
        '#type' => 'markup',
        '#markup' => $this->t(
          'The configuration has been created <pre>' . $response->getBody() . '</pre>'
        )
    ];
  }

  public function applyConfig() {

      $this->configManager

      ;

      return [];
  }

}
