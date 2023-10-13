<?php

namespace Drupal\journal_article_detail\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a block to display the statics for a Altmetric section: JCOREX-102
 *
 * @Block(
 *   id = "hwjma_dummyblock",
 *   admin_label = @Translation("dummyblock"),
 *   category = @Translation("hwjma"),
 *   context = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current Node")
 *     )
 *   }
 * )
 */
class DummyBlock extends BlockBase {
    public function build() {
        return array(
          '#type' => 'markup',
          '#markup' => 'This block is for Dummy content',
        );
      }
}
