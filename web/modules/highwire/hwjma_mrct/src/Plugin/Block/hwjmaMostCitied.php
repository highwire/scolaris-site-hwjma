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
 *  id = "highwire_most_cited_block",
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
class HwjmaMostCitied extends BlockBase implements ContainerFactoryPluginInterface {

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
   * @inheritdoc
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['read_cited'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#multiple' => FALSE,
      '#title' => $this->t('Most Read or Most Cited'),
      '#options' => [
        'most-read' => 'Most Read',
        'most-cited' => 'Most Cited',
      ],
      '#default_value' => isset($config['read_cited']) ? $config['read_cited'] : 'most-read',
    ];

    $view_modes = $this->entityManager->getViewModes('node');
    $view_modes_list = [];

    foreach ($view_modes as $key => $view_mode) {
      $view_modes_list[$key] = $view_mode['label'];
    }

    $form['view_mode'] = [
      '#type' => 'select',
      '#multiple' => FALSE,
      '#title' => $this->t('View mode'),
      '#description' => $this->t('What view mode to use to render the articles?'),
      '#options' => $view_modes_list,
      '#default_value' => isset($config['view_mode']) ? $config['view_mode'] : 'toc_list',
    ];

    $form['limit'] = [
      '#type' => 'number',
      '#multiple' => FALSE,
      '#title' => $this->t('Limit'),
      '#description' => $this->t('Limit the number of articles rendered'),
      '#default_value' => isset($config['limit']) ? $config['limit'] : '',
    ];

    $form['corpus'] = [
      '#type' => 'textfield',
      '#multiple' => FALSE,
      '#title' => $this->t('Optional: Corpus Code'),
      '#description' => $this->t('If no node context is provided, you may instead manually provide a corpus code. You must either supply a node context or a corpus code. If both are supplied, the node-context takes precedence.'),
      '#default_value' => isset($config['corpus']) ? $config['corpus'] : '',
    ];

    return $form;
  }

  /**
   * @inheritdoc
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    foreach (['read_cited', 'view_mode', 'limit', 'corpus'] as $option) {
      $this->configuration[$option] = $values[$option];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configuration;
    $querypage = $config['page'];
    
    $corpus = '';
    try {
      $node = $this->getContextValue('node');
     /* if ($node->hasField('corpus') && !$node->get('corpus')->isEmpty()) {
        $corpus = $node->get('corpus')->getString();
      } */
      $corpus = $config['corpus'];
    }
    catch (\Exception $e) {
      $corpus = $config['corpus'];
    }
    if (!$corpus) {
      return [];
    }

    // Get list of apaths
    $cache_key = serialize($config) . $corpus;
    if ($cache = $this->cacheDefault->get($cache_key)) {
      $apaths = $cache->data; 
    }
    else {
      
      if ($config['read_cited'] == 'most-cited') {
        try {
          $apaths = $this->staticfs->mostCited($corpus)->getData();
        }
        catch (\Exception $e) {
          $this->logger->warning($e->getMessage());
          return [];
        }
      }

      if ($config['read_cited'] == 'most-read') {
        try {
          $apaths = $this->staticfs->mostRead($corpus)->getData();
        }
        catch (\Exception $e) {
          $this->logger->warning($e->getMessage());
          return [];
        }
      }

      $this->cacheDefault->set($cache_key, $apaths, time() + 86400);
    }

    if (!empty($apaths) && $config['limit']) {
      if(empty($querypage)) {  
        $apaths = array_slice($apaths, 0, $config['limit']);
      } else {
        $startp = $querypage+1*$config['limit'];
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

    highwire_markup_pre_fetch_markup($pre_markup_cache);
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
    $build_items = [];
    $cache_tags = [];
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

