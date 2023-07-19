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
 * Provides a 'UsageStatsData' block.
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
    'grand_total' => 'Grand Total'
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
      'query_type' => 'monthly',
      'source' => 'highwire-pmc',
      'source_total' => 0,
      'views' => ['abstract', 'full', 'pdf', 'total'],
      'date_format' => 'medium',
      'custom_date_format' => ''
    ] + parent::defaultSettings();
  }

  /**
   * @inheritdoc
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['query_type'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#multiple' => FALSE,
      '#title' => $this->t('Query Type'),
      '#description' => $this->t('Display statistics monthly or daily?'),
      '#options' => [
        'monthly' => 'Monthly Summary',
        'daily' => 'Daily (limit one year)',
      ],
      '#default_value' => isset($config['query_type']) ? $config['query_type'] : 'monthly',
    ];

    $form['views'] = [
      '#type' => 'checkboxes',
      '#required' => TRUE,
      '#multiple' => TRUE,
      '#title' => $this->t('Views'),
      '#description' => $this->t('Display statistics for these views'),
      '#options' => [
        'abstract' => 'Abstract',
        'full' => 'Full Text',
        'pdf' => 'PDF',
        'powerpoint' => 'Powerpoint',
        'total' => 'Total',
      ],
      '#default_value' => isset($config['views']) ? $config['views'] : ['abstract', 'full', 'pdf', 'total'],
    ];

    $date_formats = [];
    foreach ($this->dateFormatStorage->loadMultiple() as $machine_name => $value) {
      $date_formats[$machine_name] = $this->t('@name format: @date', ['@name' => $value->label(), '@date' => $this->dateFormatter->format(REQUEST_TIME, $machine_name)]);
    }
    $date_formats['custom'] = $this->t('Custom');

    $form['date_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Date format'),
      '#options' => $date_formats,
      '#default_value' => isset($config['date_format']) ? $config['date_format'] : 'medium',
    ];

    $form['custom_date_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom date format'),
      '#description' => $this->t('See <a href="http://php.net/manual/function.date.php" target="_blank">the documentation for PHP date formats</a>.'),
      '#default_value' => isset($config['custom_date_format']) ? $config['custom_date_format'] : '',
    ];
    $form['custom_date_format']['#states'] = [
      'visible' => [
          'select[name="settings[date_format]"]' => ['value' => 'custom']
      ]
    ];

    $form['limit'] = [
      '#type' => 'numeric',
      '#title' => $this->t('Limit'),
      '#description' => $this->t('Limit to this number of rows'),
      '#default_value' => isset($config['limit']) ? $config['limit'] : '',
    ];

    return $form;
  }

  /**
   * @inheritdoc
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    foreach (['query_type', 'source', 'source_total', 'views', 'date_format', 'custom_date_format'] as $option) {
      if (is_array($values[$option])) {
        $values[$option] = array_filter($values[$option]);
      }
      $this->configuration[$option] = $values[$option];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    
    $node = $this->getContextValue('node');
    $config = $this->getConfiguration();
    
    // Date format
    if ($config['date_format'] == 'custom') {
      $date_format = $config['custom_date_format'];
    }
    else {
      $format_entity = $this->dateFormatStorage->load($config['date_format']);
      $date_format = $format_entity->getPattern();
    }

    // Apath
    //$apath = $node && $node->hasField('apath') && !$node->get('apath')->isEmpty() ? $node->get('apath')->first()->getString() : '';
    $apath = '/biorxiv/early/2017/03/15/117101.atom';
    if (empty($apath)) {
      return $build;
    }
    
    // Get usage Stats
    $stats = [];
    try {
      if ($config['query_type'] == 'monthly') {
        $stats = $this->usageStats->singleArticle($apath)->getData();
      }
      if ($config['query_type'] == 'daily') {
        $stats = $this->usageStats->singleArticleYear($apath)->getData();
      }
    }
    catch (\Exception $e) {
      return $build;
    }
    if (empty($stats)) {
      return $build;
    }

    // Render table
    $build['usage_stats_data'] = [
      '#type' => 'table',
      '#caption' => $this->t('Article Usage Data'),
      '#prefix' => '<div class="ajax-wrapper">',
      '#suffix' => '</div>',
    ];
    $build['usage_stats_data']['#header'] = [
      'date' => 'Period',
    ];
    foreach ($config['views'] as $view) {
      $build['usage_stats_data']['#header'][$view] = $this->view_labels[$view];
    }
    //dump($stats);
    foreach ($stats as $i => $row) {
      $build['usage_stats_data'][$i] = [];

      $date = $this->renderDate($date_format, $row['year'], $row['month'], $row['day'] ?? NULL);
      $build['usage_stats_data'][$i]['date']['#markup'] = $date;

      $total = 0;
      foreach ($config['views'] as $view) {
        if (isset($row[$view])) {
          $build['usage_stats_data'][$i][$view]['#markup'] = $row[$view];
          $total += $row[$view];
        }
      }
      if (in_array('total', $config['views'])) {
        $build['usage_stats_data'][$i]['total']['#markup'] = $total;
      }
    }

    $build['#attached']['library'][] = 'journal_article_detail/usagestatsdata';    
  //  $build['#theme'] = 'hwjma_usage_stats_data';
    //dump($build);
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