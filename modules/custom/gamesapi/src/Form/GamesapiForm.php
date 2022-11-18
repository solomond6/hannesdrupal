<?php

namespace Drupal\gamesapi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class GamesapiForm.
 *
 * @package Drupal\gamesapi\Form
 */
class GamesapiForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gamesapi_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $conn = Database::getConnection();
    $record = array();
    if (isset($_GET['num'])) {
        $query = $conn->select('gamesapi', 'm')
            ->condition('id', $_GET['num'])
            ->fields('m');
        $record = $query->execute()->fetchAssoc();

    }

    $form['api_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Api key:'),
      '#required' => TRUE,
       //'#default_values' => array(array('id')),
      '#default_value' => (isset($record['api_key']) && $_GET['num']) ? $record['api_key']:'',
    );

    $form['api_secret'] = array(
      '#type' => 'textfield',
      '#title' => t('Api Secret:'),
      '#default_value' => (isset($record['api_secret']) && $_GET['num']) ? $record['api_secret']:'',
    );

    
    $form['type'] = array (
    '#type' => 'select',
    '#title' => ('Type'),
    '#options' => array(
      'Test' => t('Test'),
      'Live' => t('Live'),
      '#default_value' => (isset($record['type']) && $_GET['num']) ? $record['type']:'',
      ),
    );


    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'save',
    ];

    return $form;
  }

  /**
    * {@inheritdoc}
    */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $api_key = $form_state->getValue('api_key');
    if(preg_match('/[^A-Za-z]/', $api_key)) {
       $form_state->setErrorByName('api_key', $this->t('api key must in characters without space'));
    }

    if (empty($api_secret)){
      // Set an error for the form element with a key of "api_secret".
      $form_state->setErrorByName('api_secret', $this->t('Cannont be empty'));
    }

    if (empty($type)){
      // Set an error for the form element with a key of "type".
      $form_state->setErrorByName('type', $this->t('Cannont be empty'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $field=$form_state->getValues();
    $api_key=$field['api_key'];
    $api_secret=$field['api_secret'];
    $type=$field['type'];

    if (isset($_GET['num'])) {
          $field  = array(
              'api_key'   => $api_key,
              'api_secret' =>  $api_secret,
              'type' =>  $type,
          );
          $query = \Drupal::database();
          $query->update('gamesapi')
              ->fields($field)
              ->condition('id', $_GET['num'])
              ->execute();
          drupal_set_message("succesfully updated");
          $form_state->setRedirect('gamesapi.display_table_controller_display');

      }

       else
       {
           $field  = array(
              'name'   =>  $name,
              'mobilenumber' =>  $number,
              'email' =>  $email,
              'age' => $age,
              'gender' => $gender,
              'website' => $website,
          );
           $query = \Drupal::database();
           $query ->insert('gamesapi')
               ->fields($field)
               ->execute();
           drupal_set_message("succesfully saved");

           $response = new RedirectResponse("/gamesapi/hello/table");
           $response->send();
       }
     }

}
