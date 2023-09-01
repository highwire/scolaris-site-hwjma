<?php

namespace  Drupal\hwjma_mrct;
/* use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use HighWire\Clients\Staticfs\Staticfs;
use Drupal\highwire_content\Lookup;
use Drupal\Core\Cache\CacheBackendInterface; */

class Mostcitedread   {

  /**
   * Staticfs client.
   *
   * @var \HighWire\Clients\Staticfs\Staticfs
   */
  //protected $staticfs;


  /*
  /**
   * Constructs a new TableOfContentsBlock object.
   *
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
 /* 
   public function __construct(
    Lookup $lookup,
    EntityTypeManagerInterface $entity_manager,
    Staticfs $staticfs,
    CacheBackendInterface $cache_default
  ) {
   
    $this->lookup = $lookup;
    $this->entityManager = $entity_manager;
    $this->staticfs = $staticfs;
    $this->cacheDefault = $cache_default;
    
  }

 
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('highwire_content.lookup'),
      $container->get('entity.manager'),
      $container->get('hwphp.staticfs'),
      $container->get('cache.default')
   //   $container->get('logger.factory')
    );
  }
*/

/* public function __construct(Staticfs $staticfs) {
  $this->staticfs = $staticfs;  
}

 /**
   * {@inheritdoc}
   */
/*public static function create(ContainerInterface $container) {
  return new static(
    $container->get('hwphp.staticfs')
  );
} */
public function get_number() {
  return rand(1000,9999);
}
  public function fetchmostcitedread() {
    $corpus = '';
    //echo "hello clal"; die;
    return "custom services";
    try {
      $node = $this->getContextValue('node');
      if ($node->hasField('corpus') && !$node->get('corpus')->isEmpty()) {
        $corpus = $node->get('corpus')->getString();
      }
    }
    catch (\Exception $e) {
      $corpus = $corpus;
    }
    if (!$corpus) {
      return [];
    }

    if($tab=='most-cited') {
      $apaths = $this->staticfs->mostCited($corpus)->getData();
      }
      if($tab=='most-read') {
        $apaths = $this->staticfs->mostRead($corpus)->getData();
        $this->logger->warning($e->getMessage());
        return [];
    }
    if (!empty($apaths) && $end) {
      $apaths = array_slice($apaths,$start, $end);
    }
    if (empty($apaths)) {
      return [];
    }
    $nids = $this->lookup->nidsFromApaths($apaths);
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
      return $apath;  
      // Prime markup caches
      /*$display_mode = entity_get_display('node', $node->getType(), $config['view_mode']);
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
      } */

    }
  }
}

