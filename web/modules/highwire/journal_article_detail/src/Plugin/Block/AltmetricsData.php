<?php
/**
 * @file
 * Contains \Drupal\journal_article_detail\Plugin\Block\AltmetricsData.
 */

namespace Drupal\journal_article_detail\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "hwjma_altmetrics_data",
 *   admin_label = @Translation("Altmetrics Data"),
 *   category = @Translation("Altmetrics"),
 * )
 */
class AltmetricsData extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $altmetrics_data = '';
    $config = $this->getConfiguration();
    
    $page = pager_find_page();
    $num_per_page = $config['number_per_page'];

    $parameters = array(
      'issns' => $config['issns'],
      'page' => !empty($page) ? $page + 1 : 1,
      'num_results' => $config['number_per_page'],
    );
    $query = http_build_query($parameters, '', '&');
    
    $api_url = isset($config['api_url']) && !empty($config['api_url']) ? $config['api_url'] : 'https://api.altmetric.com/v1/citations/';
    $url = $api_url . $config['month'] . '?' . $query;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $json_string = curl_exec($ch);
    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
 
    $results = [];
    if ($response_code == 200 && !empty($json_string)) {
      $altmetrics_data = json_decode($json_string, TRUE);
      if(!empty($altmetrics_data)){
        $results = $altmetrics_data['results'];
      }
    }

    if($config['show_pager'] == 1){
      $offset = $num_per_page * $page;
      pager_default_initialize($altmetrics_data['query']['total'], $num_per_page);
      $build = [
        '#theme' => 'altmetrics_data',
        '#altmetrics_data' => $results,
        '#altmetrice_setting' => $config,
        '#pager' => [
          '#type' => 'pager',
        ],
      ];
    }
    else{
      $build = [
        '#theme' => 'altmetrics_data',
        '#altmetrics_data' => $results,
        '#altmetrice_setting' => $config,
      ];
    }
    
    $build['#attached']['library'][] = 'journal_article_detail/altmetrics';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state); 
    $config = $this->getConfiguration();

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => t('Description text'),
      '#default_value' => isset($config['description']) ? $config['description'] : 'Altmetric tracks attention and engagement of scholarly articles.',
    ];
  
    $api_months_option = array('1m' => '1 month', '3m' => '3 months', '6m' => '6 months', '1y' => '1 year');
    $form['month'] = [
      '#type' => 'select',
      '#title' => t('Select Month'),
      '#options' => $api_months_option,
      '#default_value' => isset($config['month']) ? $config['month'] : '6m',
    ];

    $form['issns'] = [
      '#type' => 'textfield',
      '#title' => t('Enter ISSNs'),
      '#description' => t('ISSNs for a Journal site.'),
      '#default_value' => !empty($config['issns']) ? $config['issns'] : '',
      '#required' => TRUE,
    ];

    $form['number_per_page'] = [
      '#type' => 'textfield',
      '#title' => t('Results per page'),
      '#default_value' => !empty($config['number_per_page']) ? $config['number_per_page'] : 25,
      '#size' => '10',
      '#prefix' => '<div class="clear-block no-float">',
      '#suffix' => '</div>',
      '#description' => t('Altmetric Data to display.'),
    ]; 

    $form['api_url'] = [
      '#type' => 'textfield',
      '#title' => t('Altmetric API URL'),
      '#description' => 'Please include http:// or https:// in the URL with trailing slash. <a href="https://api.altmetric.com">' .t('Official documentation').'</a>',
      '#default_value' => !empty($config['api_url']) ? $config['api_url'] : 'https://api.altmetric.com/v1/citations/',
      '#required' => TRUE,
    ];

    $form['show_pager'] = [
      '#type' => 'checkbox',
      '#title' => t('Show pager'),
      '#default_value' => isset($config['show_pager']) ? $config['show_pager'] : 0,
      '#description' => t('Displays pager on the list.'),
    ];
  
    $form['more_link'] = [
      '#type' => 'checkbox',
      '#title' => t('Show More Link'),
      '#default_value' => isset($config['more_link']) ? $config['more_link'] : 0,
      '#prefix' => '<div class="clear-block no-float">',
      '#suffix' => '</div>',
    ];
  
    $form['more_link_label'] = [
      '#type' => 'textfield',
      '#title' => t('More Link Label'),
      '#default_value' => isset($config['more_link_label']) ? $config['more_link_label'] : 'See more',
      '#states' => array(
        'visible' => array(
          ':input[name="settings[more_link]"]' => array('checked' => TRUE),
        ),
      ),
    ];

    $form['more_link_url'] = [
      '#type' => 'textfield',
      '#title' => t('More Link URL'),
      '#default_value' => isset($config['more_link_url']) ? $config['more_link_url'] : '',
      '#states' => array(
        'visible' => array(
          ':input[name="settings[more_link]"]' => array('checked' => TRUE),
        ),
      ),
    ];

    $form['msg_no_data'] = [
      '#type' => 'textfield',
      '#title' => t('No Altmetric data'),
      '#description' => t('Message to show if there is no Altmetric data.'),
      '#default_value' => isset($config['msg_no_data']) ? $config['msg_no_data'] : 'No Altmetric data available.',
    ]; 

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $data = ['label', 'label_display', 'description', 'month', 'issns', 'number_per_page', 'api_url', 'show_pager', 'more_link', 'more_link_label', 'more_link_url', 'msg_no_data'];
    foreach ($data as $option) {
      $this->configuration[$option] = $values[$option];
    }
  }
}
