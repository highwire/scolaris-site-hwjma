<?php

namespace Drupal\journal_article_detail\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use HighWire\Clients\UsageStats\UsageStats;

/**
 * Provides a 'UsageStatsData' block: JCOREX-102
 *
 * @Block(
 *  id = "hwjma_usage_stats",
 *  admin_label = @Translation("Article Usage Statistics Table"),
 *  context = {
 *    "node" = @ContextDefinition(
 *      "entity:node",
 *      label = @Translation("Current Node")
 *    )
 *  }
 * )
 */
class UsageStatsData extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * UsageStats service client.
   *
   * @var \HighWire\Clients\UsageStats\UsageStats
   */
  protected $usageStats;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The date format entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateFormatStorage;

  /**
   * Default labels for views.
   *
   * @var array
   */
  protected $view_labels = [
    'abstract' => 'Abstract',
    'full' => 'Full',
    'pdf' => 'PDF',
    'powerpoint' => 'PowerPoint',
    'total' => 'Total',
    'grand_total' => 'Grand Total',
    'source' => 'Source'
  ];

  /**
   * Constructs a new UsageStatsData object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \HighWire\Clients\UsageStats\UsageStats $usage_stats
   *   UsageStats service client.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $date_format_storage
   *   The date format storage.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    UsageStats $usage_stats,
    DateFormatterInterface $date_formatter, 
    EntityStorageInterface $date_format_storage
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->usageStats = $usage_stats;
    $this->dateFormatter = $date_formatter;
    $this->dateFormatStorage = $date_format_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('hwphp.usage-stats'),
      $container->get('date.formatter'),
      $container->get('entity.manager')->getStorage('date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'query_type' => 'ArticleLifetime',
      'source' => 'highwire-pmc',
      'source_total' => 0,
      'views' => ['abstract', 'full', 'pdf', 'total'],
      'date_format' => 'medium',
      'custom_date_format' => ''
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $node = $this->getContextValue('node');
    $config = $this->getConfiguration();
    $usageStatsConfig = \Drupal::config('journal_article_detail.settings');
    $emptyErrorText = $usageStatsConfig->get('empty_text');
    $showSource = $usageStatsConfig->get('show_source');
    $defaultSourceName = $usageStatsConfig->get('default_source_name');
    $usageMetricTypesFilter = $usageStatsConfig->get('metric_types');
    $displayGrandTotal = $usageStatsConfig->get('display_grand_total');
    $errorDisplay = [
      '#type' => 'markup',
      '#markup' => $emptyErrorText,
      '#prefix' => '<div class="ajax-wrapper error-msg">',
      '#suffix' => '</div>',
    ];
    // Get date format from config
    if ($config['date_format'] == 'custom') {
      $date_format = $config['custom_date_format'];
    }
    else {
      $format_entity = $this->dateFormatStorage->load($config['date_format']);
      $date_format = $format_entity->getPattern();
    }
    // Get Apath
    // HWJMA Usage stats data not available so that use other hardcoded apath when data available then remove hardcoded value
    //$apath = $node && $node->hasField('apath') && !$node->get('apath')->isEmpty() ? $node->get('apath')->first()->getString() : '';
    $apath = '/cmaj/195/21/E748.atom';
    if (empty($apath)) {
      return $errorDisplay;
    }
    $stats = [];
    $options = [];
    // Check date fromat is valid or not
    if (!empty($config['fromDate']) && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$config['fromDate'])) {
      $options['from-date'] = $config['fromDate'];
    }
    if (!empty($config['toDate']) && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$config['toDate'])) {
      $options['to-date'] = $config['toDate'];
    }
    // Call singleArticle() for get stats data
    try {
        $stats = $this->usageStats->singleArticle($apath, 'true', $options)->getData();
    }
    catch (\Exception $e) {
      return $build;
    }
    if (empty($stats)) {
      return $errorDisplay;
    }
    // Render usage stats data table
    $build['usage_stats_data'] = [
      '#type' => 'table',
      '#prefix' => '<div class="ajax-wrapper usage-stats-data-table">',
      '#suffix' => '</div>',
    ];
    $build['usage_stats_data']['#header'] = [
      'date' => 'Period',
    ];
    $counter = 0;
    // Prepare header for table view : Abstract, PDF, Full
    foreach ($config['views'] as $view) {
      $build['usage_stats_data']['#header'][$view] = $this->view_labels[$view];
    }
    $grand_total = [];
    // Prepare data for table view : Abstract, PDF, Full
    foreach ($stats as $i => $row) {
      $build['usage_stats_data'][$i] = [];
      $date = $this->renderDate($date_format, $row['year'], $row['month'], $row['day'] ?? NULL);
      $build['usage_stats_data'][$i]['date']['#markup'] = $date;
      $total = 0;
      foreach ($config['views'] as $view) {
        if (isset($row[$view])) {
          $build['usage_stats_data'][$i][$view]['#markup'] = $row[$view];
          $grand_total[$view] += $row[$view];
          $total += $row[$view];
        }
      }
      if (in_array('total', $config['views'])) {
        $build['usage_stats_data'][$i]['total']['#markup'] = $total;
        $addColTotal += $total;
      }
      // Add source header and value in usage stats data table  if required.
      if (!empty($usageMetricTypesFilter['source']) && in_array('source', $config['views'])) {
        // Get default source if set.
        if ($row['platform'] == 'highwire' && !empty($defaultSourceName)) {
          $build['usage_stats_data'][$i]['source']['#markup'] = $defaultSourceName;
        }
        else {
          $build['usage_stats_data'][$i]['source']['#markup'] = $row['platform'];
        }
      }
      $counter = $i;
    }
    // Add grand total row in usage stats data table if required
    if ($displayGrandTotal == 1) {
      $build['usage_stats_data'][$counter+1]['date']['#markup'] = 'Grand Total';
      $innerCounter = 0;
      foreach ($grand_total as $k => $val) {
        $build['usage_stats_data'][$counter+1][$k]['#markup'] = $val;
        $innerCounter = $k;
      }
      if (in_array('total', $config['views'])) {
        $build['usage_stats_data'][$counter+1][$innerCounter+1]['#markup'] = $addColTotal;
      }
      if (!empty($usageMetricTypesFilter['source'])) {
        $build['usage_stats_data'][$counter+1][$innerCounter+2]['#markup'] = '';
      }
    }
    $build['#attached']['library'][] = 'journal_article_detail/usagestatsdata';    
    return $build;
  }

  /**
   * Given a format, a year, month and optionally a day, render a date string.
   * 
   * The format may be modified to remove non-applicable placeholders.
   *
   * @param string $date_format
   *   A date format.
   * @param string $year
   *   The date year as a string.
   * @param string $month
   *   The date month as an integer string.
   * @param string|null $day
   *   The day of the month as an integer string.
   * 
   * @return string
   *   The formatted date.
   */
  protected function renderDate(string $date_format, string $year, string $month, $day = NULL): string {
    // Best-effort fixing of date-formats. Proper formatting of dates without
    // days or times may require a new or custom format to be defined.

    // Remove time elements, they are never applicable.
    $date_format = strtr($date_format, ['a' => '', 'A' => '', 'B' => '', 'g' => '', 'G' => '', 'h' => '', 'H' => '', 'i' => '', 's' => '']);

    // If day is NULL, then strip out day elements from the date format.
    if ($day === NULL) {
      $date_format = strtr($date_format, ['d' => '', 'D' => '', 'j' => '', 'l' => '', 'N' => '', 'S' => '', 'z' => '', 'W' => '']);
    }
    $date_format = trim($date_format, ':/-, ');

    // Render the date
    $date = new \DateTime();
    $date->setDate($year, $month, $day ?? 1);
    return $date->format($date_format);
  }
}
