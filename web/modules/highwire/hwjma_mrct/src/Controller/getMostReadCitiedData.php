<?php

namespace Drupal\hwjma_mrct\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\views\Element\View;
use Drupal\views\Views;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;
class getMostReadCitiedData extends ControllerBase  {

  function getData ($corpus,$tab) {
    $build = [];
    $block_manager = \Drupal::service('plugin.manager.block');
    $mostCitedtotal =  $block_manager->createInstance('most_read_cited_block', [
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
    $citedData = $mostCitedtotal->build();
    
    foreach($citedData['#items'] as $rows) {
      $node = $rows['#node'];
      $title = $node->get('title')->value;
      $nid = $node->get('nid')->value;
      $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$nid);
      $most_citedTotal[$alias] =  $title;
    }
    $num_per_page = 3;
    pager_default_initialize(count($most_citedTotal), $num_per_page);
    $mostCited1 =  $block_manager->createInstance('most_read_cited_block', [
      'read_cited' => $tab,
      'view_mode' => 'default',    
      'limit' => 3,
      'page' => $querypage ,
      'label' => '',
      'corpus' => $corpus,
    ]);
    $citedData1 = $mostCited1->build();
   
    foreach($citedData1['#items'] as $rows) {
      $node = $rows['#node'];
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
      $content_node = Node::load($nid);
      $view_mode = 'content_details';
      $content_details = render(\Drupal::entityTypeManager()->getViewBuilder('node')->view($content_node, $view_mode));
      $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$nid);
      $most_cited1[] =  ['title' => $title , 'link' => $alias , 'date_epub' => $epubdate,
                         'fpage' => $fpage, 'lpage' => $lpage , 'doi' => $doi , 'volume' => $volume ,
                         'issue' => $issue , 'journal_title' => $journal_title , 'authors_full_name' => $authors
                        ];
    }
    $build = [
      '#theme' => 'mostreadcited',
      '#mostreadcited' => $most_cited1,
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
