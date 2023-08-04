<?php

/**
 * @file
 * Contains Drupal\journal_article_detail\Form\UsageStatsSettingsForm.
 */

namespace Drupal\journal_article_detail\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UsageStatsSettingsForm.
 *
 * @package Drupal\journal_article_detail\Form
 * Use for Altmetric and Usage Stats backend configuration form : JCOREX-102
 */
class UsageStatsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'journal_article_detail.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'usage_stats_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('journal_article_detail.settings');

    $form['altmetric_override_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Altmetric Title'),
      '#default_value' => !empty($config->get('altmetric_override_title')) ? $config->get('altmetric_override_title') : 'Statistics from Altmetric.com',
    );

    $form['altmetric_statistics_tag'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Altmetric Statistics Custom Tag'),
      '#default_value' => !empty($config->get('altmetric_statistics_tag')) ? $config->get('altmetric_statistics_tag') : '<div data-badge-details="right" data-badge-type="medium-donut" data-doi="'.$doi.'" data-hide-no-mentions="true" class="altmetric-embed"></div>',
    );

    $form['override_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Usage Stats Title'),
      '#default_value' => !empty($config->get('override_title')) ? $config->get('override_title') : 'Usage statistics',
    );

    $form['date_filters'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('DATE FILTERS'),
      '#options' => array('ArticleLifetime' => $this->t('Article lifetime'), 'LastSixMonths' => $this->t('Last 6 Months'),  'ThisMonth' => $this->t('This Month'), 'ViewRange' => $this->t('View range')),
      '#default_value' => !empty($config->get('date_filters')) ? $config->get('date_filters') : array('ArticleLifetime', 'LastSixMonths', 'ThisMonth'),
    );

    $form['metric_types'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('SELECT WHICH TYPE OF METRICS TO SHOW ON THE TABLE.'),
      '#options' => array('abstract' => $this->t('Abstract'), 'full' => $this->t('Full'),  'pdf' => $this->t('PDF'),  'total' => $this->t('Total'),  'source' => $this->t('Source')),
      '#default_value' => !empty($config->get('metric_types')) ? $config->get('metric_types') : array('Abstract', 'Full', 'PDF'),
    );

    $form['default_view'] = [
        '#type' => 'radios',
        '#title' => $this->t('DEFAULT VIEW'),
        '#options' => [
          'ArticleLifetime' => $this->t('Article lifetime'),
          'LastSixMonths' => $this->t('Last 6 Months'),
          'ThisMonth' => $this->t('This Month'),
        ],
        '#default_value' => !empty($config->get('default_view')) ? $config->get('default_view') : 'ArticleLifetime',
    ];
    
    $form['display_settings'] = [
        '#type' => 'radios',
        '#title' => $this->t('DISPLAY SETTINGS'),
        '#options' => [
          0 => $this->t('Chart Only'),
          1 => $this->t('Table Only'),
          2 => $this->t('Both Chart and Table'),
        ],
        '#default_value' => !empty($config->get('display_settings')) ? $config->get('display_settings') : 1,
        '#attributes' => ['data-default-value' => 1],
    ];

    $form['empty_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('EMPTY TEXT'),
      '#default_value' => !empty($config->get('empty_text')) ? $config->get('empty_text') : 'No statistics are available.',
    );

    $form['default_source_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('DEFAULT SOURCE NAME'),
      '#default_value' => !empty($config->get('default_source_name')) ? $config->get('default_source_name') : t('Highwire'),
    );

    $form['display_grand_total'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display Grand Total'),
      '#default_value' => !empty($config->get('display_grand_total')) ? $config->get('display_grand_total') : 0,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('journal_article_detail.settings')
      ->set('default_view', $form_state->getValue('default_view'))
      ->set('date_filters', $form_state->getValue('date_filters'))
      ->set('display_settings', $form_state->getValue('display_settings'))
      ->set('metric_types', $form_state->getValue('metric_types'))
      ->set('override_title', $form_state->getValue('override_title'))
      ->set('altmetric_statistics_tag', $form_state->getValue('altmetric_statistics_tag'))
      ->set('altmetric_override_title', $form_state->getValue('altmetric_override_title'))
      ->set('empty_text', $form_state->getValue('empty_text'))
      ->set('default_source_name', $form_state->getValue('default_source_name'))
      ->set('display_grand_total', $form_state->getValue('display_grand_total'))
      ->save();
  }
}
