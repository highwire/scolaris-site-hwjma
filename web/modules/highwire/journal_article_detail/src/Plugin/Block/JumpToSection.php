<?php
/**
 * @file
 * Contains \Drupal\journal_article_detail\Plugin\Block\JumpToSection.
 */
namespace Drupal\journal_article_detail\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup as CoreMarkup;
use Drupal\Core\Url;
use Drupal\highwire_markup\Plugin\Block\Markup;
use Drupal\highwire_content\HighWireContent;
/**
 * Provides a block for Jump to Section : JCOREX-101.
 *
 * @Block(
 *   id = "jump_to_section",
 *   admin_label = @Translation("Jump to Section"),
 *   category = @Translation("Jump to Section"),
 *   context = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current Node")
 *     )
 *   } 
 * )
 */
class JumpToSection extends Markup {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block = parent::build();
    $node = $this->getContextValue('node');
    $apath = '';
    // Get apath from node
    if ($node->hasField('apath') && !$node->get('apath')->isEmpty()) {
      $apath = $node->get('apath')->getString();
    }
    if (empty($apath)) {
      return $block;
    }

    $config = $this->configuration;
    $add_items = [];
    $add_tab_items = [];
    $add_default_tab_items = [];

    // Add Article tab as a parent default tab
    $add_default_tab_items['article'] = [
      "#type" => "link",
      "#title" => "Articles",
      "#url" => Url::fromUserInput('#edit-group-article-label'),
      "#attributes" => ['id'=> 'jump-article-label', 'data-jump-parent' => 'true', 'class' => 'edit-group-article-label'],
    ];

    // Check for abstract markup and add abstract link if it exists.
    if (!empty($config['include_abstract']) && !empty($config['abstract_settings']['markup']) && !empty($config['abstract_settings']['title']) && !empty($config['abstract_settings']['id'])) {
      $markup_config = $this->entityTypeManager->getStorage('markup')->load($config['abstract_settings']['markup']);
      $abstract_build = [];
      if ($markup_config) {
        $abstract_build = $markup_config->getRenderArray([$apath], [], $node);
      }
      if (!empty($abstract_build)) {
        $abstract_link = new Link(CoreMarkup::create($config['abstract_settings']['title']), Url::fromUserInput('#' . $config['abstract_settings']['id']));
        $add_items['abstract'] = $abstract_link->toRenderable();
      }
    }
    
    // Check for Figures & Tables markup and add Figures & Tables link
    if (!empty($node->hasField('has_figures_or_tables')) && $node->hasField('has_figures_or_tables') == true) {
      $add_tab_items['figures_tables'] = [
        "#type" => "link",
        "#title" => "Figures & Tables",
        "#url" => Url::fromUserInput('#edit-group-figures-tables-label'),
        "#attributes" => ['id'=> 'jump-figures_tables-label', 'data-jump-parent' => 'true', 'class' => 'edit-group-figures-tables-label'],
      ];
    }

    // Add metrics link in Jump to Section
    $add_tab_items['metrics'] = [
      "#type" => "link",
      "#title" => "Metrics",
      "#url" => Url::fromUserInput('#edit-group-metrics-label'),
      "#attributes" => ['id'=> 'jump-metrics-label', 'data-jump-parent' => 'true', 'class' => 'edit-group-metrics-label'],
    ];
    
    // Prepare Article abstract sub items.
    if (!empty($add_items) ) {
      array_unshift(
        $block[$apath]['#items'],
        $add_items
      );
    }

    // Add class for Article sub heading links
    foreach ($block as $key => $value) {
      $block[$key]['#attributes'] = ['class' => 'tab-margin-30'];
    }

    // Prepare Article link
    if (!empty($add_default_tab_items) ) {
      array_unshift(
        $block,
        [
          '#theme' => 'item_list',
          '#items' => $add_default_tab_items,
          '#context' => ['list_style' => 'highwire_content_nav'],
        ]
      );
    }

    // Add other parent tab items exclude Article tab
    if (!empty($add_tab_items)) {
      array_push(
        $block,
        [
          '#theme' => 'item_list',
          '#items' => $add_tab_items,
          '#context' => ['list_style' => 'highwire_content_nav'],
        ]
      );
    }
    return $block;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Abstract link settings.
    $form['include_abstract'] = [
      '#type' => 'checkbox',
      '#title' => t('Include abstract link'),
      '#default_value' => $this->configuration['include_abstract'],
    ];
    $form['abstract_settings'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="settings[include_abstract]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['abstract_settings']['markup'] = $form['markup'];
    $form['abstract_settings']['markup']['#title'] = t('Markup profile for abstract');
    $form['abstract_settings']['markup']['#default_value'] = $this->configuration['abstract_settings']['markup'];
    $form['abstract_settings']['title'] = [
      '#type' => 'textfield',
      '#title' => t('Abstract Link Title'),
      '#default_value' => $this->configuration['abstract_settings']['title'],
    ];
    $form['abstract_settings']['id'] = [
      '#type' => 'textfield',
      '#title' => t('Abstract Link ID'),
      '#default_value' => $this->configuration['abstract_settings']['id'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    foreach (['include_abstract', 'abstract_settings'] as $field) {
      $this->setConfigurationValue($field, $form_state->getValue($field));
    }
  }
}
