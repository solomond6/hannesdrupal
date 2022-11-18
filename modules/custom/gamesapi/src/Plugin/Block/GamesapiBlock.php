<?php

namespace Drupal\gamesapi\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'GamesapiBlock' block.
 *
 * @Block(
 *  id = "gamesapi_block",
 *  admin_label = @Translation("Gamesapi block"),
 * )
 */
class GamesapiBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    ////$build = [];
    //$build['gamesapi_block']['#markup'] = 'Implement GamesapiBlock.';

    $form = \Drupal::formBuilder()->getForm('Drupal\gamesapi\Form\GamesapiForm');

    return $form;
  }

}
