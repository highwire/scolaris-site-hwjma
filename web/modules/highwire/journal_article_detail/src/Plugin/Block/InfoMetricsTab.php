<?php
/**
 * @file
 * Contains \Drupal\journal_article_detail\Plugin\Block\XaiBlock.
 */
namespace Drupal\journal_article_detail\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block that displays Info & Metrics tab: JCOREX-102
 *
 * @Block(
 *  id = "hwjma_info_metrics_tab",
 *  admin_label = @Translation("Info & Metrics"),
 *  context = {
 *    "node" = @ContextDefinition(
 *      "entity:node",
 *      label = @Translation("Current Node")
 *    )
 *  }
 * )
 */
class InfoMetricsTab extends BlockBase implements ContainerFactoryPluginInterface {

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

  public function __construct(array $configuration, $plugin_id, $plugin_definition,
    RouteMatchInterface $route_match
  ) {
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
    // Journal author variables
    $authors = $node->get('authors_full_name')->getValue();
    $author_names = [];
    foreach ($authors as $key => $value) {
        $author_names[$key] = $value['value'];
    }
    $authors_name = $author_names;
    // Journal description variables
    $journal_doi = $node->get('doi')->getString();
    //New way to display DOI
    if (!empty($journal_doi)) {
        $doi_link = 'https://doi.org/' . $journal_doi;
    }
    $journal_title = $node->get('journal_title')->getValue()['0']['value'];
    $eissn = $node->get('journal_eissn')->getString();
    $pissn = $node->get('journal_pissn')->getString();
    $issue = $node->get('issue')->getValue();
    $issue = $issue[0]['value'];
    $volume = !$node->get('volume')->isEmpty() ? $node->get('volume')->getString() : '';
    $date_released = $node->get('date_epub_original')->getValue()['0']['original'];
    $build = [
        '#theme' => 'hwjma_info_metrics_tab',
        '#journal_title' => $journal_title,
        '#authors_name' => $authors_name,
        '#doi' => $journal_doi,
        '#doi_link' => $doi_link,
        '#date_released' => $date_released,
        '#eissn' => $eissn,
        '#pissn' => $pissn,
        '#issue' => $issue,
        '#volume' => $volume
    ];
    return $build;
  }
}
