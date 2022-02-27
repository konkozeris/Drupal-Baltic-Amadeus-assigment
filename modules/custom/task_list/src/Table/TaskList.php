<?php

namespace Drupal\task_list\Table;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpFoundationRedirectResponse;

class TaskList extends FormBase {
/**
 * @FormElement("tableselect");
 *
 * @param array $form
 * @param FormStateInterface $form_state
 * @return void
 */

 public function getFormId() {
   return 'task list';

 }
  public function buildForm(array $form, FormStateInterface $form_state)
  {
 // user role

  $user = \Drupal::currentUser();
  $role = $user->getRoles();

  $anonymous = 'anonymous';
  $administrator = 'administrator';
  $senior_dev = 'senior_dev';
  $junior_dev = 'junior_dev';

  if($role[0] == $anonymous) {
    \Drupal::messenger()->addError('You have no permission');
    return;
  } else {
      //rows data

      $query = \Drupal::database()->select('tasks')
      ->fields('tasks', ['id', 'task_name', 'task_url', 'papa_name', 'papa_evaluation', 'kinder_evaluation', 'created_timestamp', 'done']);
      $results = $query->execute();

      while ($result = $results->fetchAssoc())
      {
        $rows[$result['id']] = [
          'task_name' => $result['task_name'],
          'task_url' => $result['task_url'],
          'papa_name' => $result['papa_name'],
          'papa_evaluation' => $result['papa_evaluation'],
          'kinder_evaluation' => $result['kinder_evaluation'],
          'created_timestamp' => $result['created_timestamp'],
          'done' => $result['done'],
        ];
      }


  // HEADER

      $header = [
        'task_name' => $this->t('Task title'),
        'task_url' => $this->t('Task URL'),
        'papa_name' => $this->t('Supervisor name'),
        'papa_evaluation' => $this->t('Supervisor time evaluation'),
        'kinder_evaluation' => $this->t('Junior time evaluation'),
        'created_timestamp' => $this->t('Created'),
        'done' => $this->t('Finished'),
      ];

      $form['table'] = [
        '#type' => 'tableselect',
        '#header' => $header,
        '#options' => $rows,
        '#responsive' => TRUE,
        '#empty' => 'No content found',
        '#multiple' => FALSE,
      ];

      // edit and finish buttons --- check if connected user is administrator / content editor / senior

    if(isset($role) && $role[1] == $administrator || $role[1] == $senior_dev)
      {
        $form['mark_finished'] = [
            '#type' => 'submit',
            '#value' => $this->t('Mark as finished')
        ];

        $form['edit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Edit'),
            '#submit' => ['::edit_task'],
        ];

        return $form;
      }
      else
      {
        return $form;
      }
    }
  }
  public function edit_task(array $form, FormStateInterface &$form_state)
  {
    $task_id = $form_state->getValue('table');
    $path = 'edit/'.$task_id;

    $response = new HttpFoundationRedirectResponse($path);
    $response->send();

    return;

  }
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $task_id = $form_state->getValue('table');

    $connection = \Drupal::database();

    $connection->update('tasks')
    ->fields([
      'done' => 'yes',
      'end_time' => date('Y-m-d')
      ])
    ->where('id = '.$task_id.'')
    ->execute();

    \Drupal::messenger()->addStatus('Task marked as finished');
  }

}
