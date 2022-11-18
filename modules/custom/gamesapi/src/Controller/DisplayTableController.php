<?php

namespace Drupal\gamesapi\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

/**
 * Class DisplayTableController.
 *
 * @package Drupal\gamesapi\Controller
 */
class DisplayTableController extends ControllerBase {


  public function getContent() {
    // First we'll tell the user what's going on. This content can be found
    // in the twig template file: templates/description.html.twig.
    // @todo: Set up links to create nodes and point to devel module.
    $build = [
      'description' => [
        '#theme' => 'gamesapi_description',
        '#description' => 'gamesapi',
        '#attributes' => [],
      ],
    ];
    return $build;
  }

  /**
   * Display.
   *
   * @return string
   *   Return Hello string.
   */
  public function display() {
    //create table header
    $header_table = array(
      'id'=>    t('SrNo'),
      'api_key' => t('api_key'),
      'api_secret' => t('api_secret'),
      'type' => t('type'),
    );

    //select records from table
    $query = \Drupal::database()->select('gamesapi', 'm');
    $query->fields('m', ['id','api_key','api_secret','type']);
    $results = $query->execute()->fetchAll();
    $rows=array();

    foreach($results as $data){
        $delete = Url::fromUserInput('/gamesapi/form/delete/'.$data->id);
        $edit   = Url::fromUserInput('/gamesapi/form/gamesapi?num='.$data->id);

        //print the data from table
         $rows[] = array(
            'id' =>$data->id,
            'api_key' => $data->api_key,
            'mobilenumber' => $data->mobilenumber,
            'api_secret' => $data->api_secret,
            'type' => $data->type,
             \Drupal::l('Delete', $delete),
             \Drupal::l('Edit', $edit),
        );

    }
    
    //display data in site
    $form['table'] = [
            '#type' => 'table',
            '#header' => $header_table,
            '#rows' => $rows,
            '#empty' => t('No users found'),
        ];
        return $form;

  }

}
