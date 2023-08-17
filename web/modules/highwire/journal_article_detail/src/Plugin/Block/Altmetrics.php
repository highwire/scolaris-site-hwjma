<?php

namespace Drupal\journal_article_detail\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display the statics for a Altmetric section: JCOREX-102
 *
 * @Block(
 *   id = "hwjma_altmetrics",
 *   admin_label = @Translation("Altmetrics"),
 *   category = @Translation("hwjma"),
 *   context = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current Node")
 *     )
 *   }
 * )
 */
class Altmetrics extends BlockBase implements ContainerFactoryPluginInterface {
    /**
   * The node the block is displayed on.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $contextNode;
  /**
   * The currently active route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Create block to display the previous and next links for chapter/section.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The currently active route match object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    try {
      $node = $this->getContextValue('node');
    }
    catch (\Exception $e) {
      return $build;
    }
    // Get the node from context data.
    // HWJMA altmetric data not available so that use other hardcode doi when data available then remove hardcoded value
    $doi = '10.1503/cmaj.220966'; //$node->get('doi')->getString();
    // Get backend altmetric configuration settings
    $usageStatsConfig = \Drupal::config('journal_article_detail.settings');
    $altMetricTagSetting = $usageStatsConfig->get('altmetric_statistics_tag');
    $altmetric_title = $usageStatsConfig->get('altmetric_override_title');
    $altmetrics_data = str_replace('[PUBCODE]', 'data-doi='.$doi, $altMetricTagSetting);
    $build = [
      '#theme' => 'hwjma_alt_metrics',
      '#altmetrics_data' => $altmetrics_data,
      '#altmetric_title' => $altmetric_title
    ];
    $build['#attached']['library'][] = 'journal_article_detail/altmetrics';
    return $build;
  }
}
