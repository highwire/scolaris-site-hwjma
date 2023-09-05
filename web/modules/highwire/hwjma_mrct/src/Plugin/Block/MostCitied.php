<?php

/**
 * @file
 * Contains \Drupal\hwjma_mrct\Plugin\Block\MostCitied.
 */
namespace Drupal\hwjma_mrct\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use HighWire\Clients\Staticfs\Staticfs;
use Drupal\highwire_content\Lookup;
use Drupal\Core\Cache\CacheBackendInterface;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Element\View;
use Drupal\views\Views;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'MostCited' block.
 *
 * @Block(
 *   id = "most_read_cited_topics",
 *   admin_label = @Translation("Highwire Most Cited"),
 *   category = @Translation("Custom Most Cited")
 * )
 */
class MostCitied extends BlockBase implements ContainerFactoryPluginInterface  {
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
  $form['limit'] = [
    '#type' => 'number',
    '#multiple' => FALSE,
    '#required' => TRUE,
    '#title' => $this->t('Limit'),
    '#description' => $this->t('Limit the number of articles rendered'),
    '#default_value' => isset($config['limit']) ? $config['limit'] : '',
  ];

  $form['corpus'] = [
    '#type' => 'textfield',
    '#multiple' => TRUE,
    '#required' => TRUE,
    '#title' => $this->t('Corpus Code'),
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
    foreach (['limit', 'corpus'] as $option) {
      $this->configuration[$option] = $values[$option];
    }
  }

 /**
 * {@inheritdoc}
 */
  public function build() {
  $config = $this->getConfiguration();
  $block_manager = \Drupal::service('plugin.manager.block');  
  $plugin_block = $block_manager->createInstance('most_read_cited_block', [
  'read_cited' => 'most-cited',
  'view_mode' => 'default',
  'limit' => $config['limit'],
  'label' => '',
  'corpus' => $config['corpus'],
  ]);    
  $data = $plugin_block->build();
  foreach($data['#items'] as $rows) {
  $node = $rows['#node'];
  $title = $node->get('title')->value;
  $nid = $node->get('nid')->value;
  $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$nid);
  $most_cited[$alias] =  $title;
  }
  $build = [];
  $build = [
  '#theme' => 'mostcitied',
  '#most_cited' => $most_cited,
  '#corpus' => $config['corpus']
  ];
  $build['#attached']['library'][] = 'hwjma_mrct/mostreadcitedtopics';
  return $build;  
  }   
}

