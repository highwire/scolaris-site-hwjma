<?php

namespace Drupal\hwjma_mrct\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use HighWire\Clients\Staticfs\Staticfs;
use Drupal\highwire_content\Lookup;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Provides a 'HwjmaMostReadCited' block.
 *
 * @Block(
 *  id = "most_read_cited_block",
 *  admin_label = @Translation("Hwjma Most Read and Cited"),
 *  context = {
 *    "node" = @ContextDefinition(
 *      "entity:node",
 *      required = FALSE,
 *      label = @Translation("Current Node")
 *    )
 *  }
 * )
 */
class HwjmaMostReadCited extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * HighWire Content Lookup.
   *
   * @var \Drupal\highwire_content\Lookup
   */
  protected $lookup;

  /**
   * Entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Staticfs client.
   *
   * @var \HighWire\Clients\Staticfs\Staticfs
   */
  protected $staticfs;

  /**
   * Drupal default cache bin
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheDefault;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new TableOfContentsBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param Lookup $lookup
   *   HighWire Content Lookup.
   * @param EntityTypeManagerInterface $entity_manager
   *   Drupal entity manager.
   * @param \HighWire\Clients\Staticfs\Staticfs $staticfs
   *   Staticfs client.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_default
   *   Drupal default cache bin.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   The drupal logger factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Lookup $lookup,
    EntityTypeManagerInterface $entity_manager,
    Staticfs $staticfs,
    CacheBackendInterface $cache_default,
    LoggerChannelFactory $logger_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->lookup = $lookup;
    $this->entityManager = $entity_manager;
    $this->staticfs = $staticfs;
    $this->cacheDefault = $cache_default;
    $this->logger = $logger_factory->get('highwire_display');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('highwire_content.lookup'),
      $container->get('entity.manager'),
      $container->get('hwphp.staticfs'),
      $container->get('cache.default'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   * Building markup for both most cited and mostread.
   */
  public function build() {
    $config = $this->configuration;
    // Check page number for the pagination
    $querypage = $config['page'];  
    $corpus = '';
    try {
      $corpus = $config['corpus'];
    }
    catch (\Exception $e) {
      $corpus = $config['corpus'];
    }
    if (!$corpus) {
      return [];
    }

    // Get list of apaths from cache
    $cache_key = serialize($config) . $corpus;
    if ($cache = $this->cacheDefault->get($cache_key)) {
      $apaths = $cache->data; 
    }
    else {
      // Fetching the data for most cited from staticfs
      if ($config['read_cited'] == 'most-cited') {
        try {
          $apaths = $this->staticfs->mostCited($corpus)->getData();
        }
        catch (\Exception $e) {
          $this->logger->warning($e->getMessage());
          return [];
        }
      }
      // Fetching the data for most read from staticfs
      if ($config['read_cited'] == 'most-read') {
        try {
          $apaths = $this->staticfs->mostRead($corpus)->getData();
        }
        catch (\Exception $e) {
          $this->logger->warning($e->getMessage());
          return [];
        }
      }
      // Storing apaths into cache
      $this->cacheDefault->set($cache_key, $apaths, time() + 86400);
    }

    if (!empty($apaths) && $config['limit']) {
      if(empty($querypage)) {  
        $apaths = array_slice($apaths, 0, $config['limit']);
      } else {
        $startp = $querypage*$config['limit'];
        $apaths = array_slice($apaths, $startp, $config['limit']);
      }
      
    }
    if (empty($apaths)) {
      return [];
    }
   
    try {
      $nids = $this->lookup->nidsFromApaths($apaths);
    }
    catch (\Exception $e) {
      $this->logger->warning($e->getMessage());
      return [];
    }

    $nodes = Node::loadMultiple($nids);
    $pre_markup_cache = [];
    foreach ($nodes as $node) {
      $apath = '';
      if ($node->hasField('apath') && !$node->get('apath')->isEmpty()) {
        $apath = $node->get('apath')->value;
      }
      else {
        continue;
      }

      // Prime markup caches
      $display_mode = entity_get_display('node', $node->getType(), $config['view_mode']);
      $content = $display_mode->get('content');
      foreach ($content as $field => $field_display_config) {
        if (!empty($field_display_config['type']) && $field_display_config['type'] == 'hwmarkup_display_formatter') {
          $profile_id = str_replace('hwmd_', '', $field);
          if ($markup_display = \Drupal::entityTypeManager()->getStorage('markup_display')->load($profile_id)) {
            if ($profile = $markup_display->prepare($node, $apath, $node->getType())) {
              $pre_markup_cache[$profile->getProfileId()][] = $apath;
            }
          }
        }
      }
    }
     
    /**
    * Do a bulk markup request to prime the caches.
    * @param array $data
    * An array of data keyed by markup profile id and an array of apaths.
    */
    highwire_markup_pre_fetch_markup($pre_markup_cache);
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
    $build_items = [];
    $cache_tags = [];
    //Creating array to return to theme file
    foreach ($nodes as $node) {
      $build_items[] = $view_builder->view($node, $config['view_mode']);
      $cache_tags = $node->getCacheTags();
    }
    
    // Return list only if there are items to display.
    if (!empty($build_items)) {
      return [
        '#theme' => 'item_list',
        '#list_type' => 'ol',
        '#items' => $build_items,
        '#context' => ['list_style' => 'most-read-cited'],
        '#cache' => [
          'contexts' => [
            // The "current user" is used above, which depends on the request,
            // so we tell Drupal to vary by the 'user' cache context.
            'user',
          ],
          'tags' => $cache_tags,
        ],
      ];
    }
  }

}
