<?php

namespace Drupal\hwjma_mrct\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\views\Element\View;
use Drupal\views\Views;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime; // use for drupal date time
class getMostReadCitiedData extends ControllerBase  {
  /* return most cited and most read 
   * we are passing corpus, most read 
   * and most cited as argument.                                 
  */
  function getData ($corpus,$tab) {
    $build = [];
    $block_manager = \Drupal::service('plugin.manager.block');
    /* Fetching total number of records for pagination.  */ 
    $totalcount =  $block_manager->createInstance('most_read_cited_block', [
      'read_cited' => $tab,
      'view_mode' => 'default',
      'label' => '',
      'corpus' => $corpus,
    ]);
    $reqpage = \Drupal::request()->query->get('page');
    $page = pager_find_page();
    if(isset($reqpage)) {
      $querypage = $page;
    } else {
      $querypage = "";
    }
    $citedData = $totalcount->build();
    
    foreach($citedData['#items'] as $rows) {
      $node = $rows['#node'];
      $title = $node->get('title')->value;
      $nid = $node->get('nid')->value;
      $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$nid);
      $total_record[$alias] =  $title;
    }
    $num_per_page = 3;
    pager_default_initialize(count($total_record), $num_per_page);

    /* Passing limit and per page for get data for pagination.  */
    $mostCited =  $block_manager->createInstance('most_read_cited_block', [
      'read_cited' => $tab,
      'view_mode' => 'default',    
      'limit' => 3,
      'page' => $querypage ,
      'label' => '',
      'corpus' => $corpus,
    ]);
    $citedData = $mostCited->build();
    /* Fetching the data then create array to the pass twig.  */ 
    foreach($citedData['#items'] as $rows) {
      $node = $rows['#node'];
      //dump($node);
      $pdf_url=$node->get('variant_full_text_pdf')->getValue()[0]['uri'];
      if (!empty($pdf_url)) {
        $variant_full_text_pdf_url  = '/' . str_replace('sass://', 'content/', $pdf_url);
      } else {
        $variant_full_text_pdf_url = '#';
      }
      $title = $node->get('title')->value;
      $doi = $node->get('doi')->value;
      $journal_title = $node->get('journal_title')->value;
      $date_epub_original = $node->get('date_epub_original')->value;
      $epub_date_format = new DrupalDateTime($date_epub_original,'UTC' );
      $epub_date_format->setTimezone(new \DateTimeZone('America/Chicago'));
      $epubdate = $epub_date_format->format('F j, Y');
      $fpage = $node->get('fpage')->value;
      $lpage = $node->get('lpage')->value;
      $issue_id = $node->get('slug')->value;
      $volume = $node->get('volume')->value;
      $issue = $node->get('issue')->value; 
      $authors = $node->get('authors_full_name')->getValue();
      $author_names = [];
      foreach ($authors as $key => $value) {
        $author_names[$key] = $value['value'];
      }
      $authors_name = $author_names;
      $authors=implode(' ',$authors_name);
      $nid = $node->get('nid')->value;
      $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$nid);
      $most_readcited[] =  ['title' => $title , 'link' => $alias , 'date_epub' => $epubdate,
                          'fpage' => $fpage, 'lpage' => $lpage , 'doi' => $doi , 'volume' => $volume ,
                          'issue' => $issue , 'journal_title' => $journal_title , 
                          'authors_full_name' => $authors , 'pdf_download' => $variant_full_text_pdf_url ,
                        ];
    }
    /* Passing the array to twig. */ 
    $build = [
      '#theme' => 'mostreadcited',
      '#mostreadcited' => $most_readcited,
      '#fpage' => $fpage,
      '#lpage' => $lpage,
      '#pager' => [
        '#type' => 'pager',
      ],
    ];
    
    return $build;
  }
}
?>	
