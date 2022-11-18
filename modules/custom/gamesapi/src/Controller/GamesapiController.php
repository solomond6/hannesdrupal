<?php

namespace Drupal\gamesapi\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class GamesapiController.
 *
 * @package Drupal\gamesapi\Controller
 */
class GamesapiController extends ControllerBase {

  /**
   * Display.
   *
   * @return string
   *   Return Hello string.
   */
  public function display() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('This page contain all inforamtion about games api credentials')
    ];
  }

}
