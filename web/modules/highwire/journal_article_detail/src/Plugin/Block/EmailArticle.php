<?php
/**
 * @file
 * Contains \Drupal\journal_article_detail\Plugin\Block\EmailArticle.
 */
namespace Drupal\journal_article_detail\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block that displays email article in form: JCOREX-343
 *
 * @Block(
 *  id = "email_article",
 *  admin_label = @Translation("Email Article"),
 *  context = {
 *    "node" = @ContextDefinition(
 *      "entity:node",
 *      label = @Translation("Current Node")
 *    )
 *  }
 * )
 */
class EmailArticle extends BlockBase implements ContainerFactoryPluginInterface {

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
    $journalArticleDetailConfig = \Drupal::config('journal_article_detail.settings');
    $emailArticleDisplay = $journalArticleDetailConfig->get('email_article_display');
    // Check email article display configuration is true then get a from
    if (isset($emailArticleDisplay) && !empty($emailArticleDisplay)) {
        $form = \Drupal::formBuilder()->getForm('Drupal\journal_article_detail\Form\EmailArticleForm', $nid);
    }  
    $article_title = $node->get('title')->getString();
    $article_title_link = $node->get('apath')->getString();
    $build = [
        '#theme' => 'email_article',
        '#article_title' => $article_title,
        '#article_title_link' => $article_title_link,
        '#email_article_display' => $emailArticleDisplay,
        '#custom_form' => $form
    ];
    return $build;
  }
}
