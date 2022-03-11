<?php

namespace Drupal\edit_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EditForm extends FormBase
{
    public function getFormId()
    {
      return 'edit_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state, $task_id = NULL)
    {
      // user role

      $user = \Drupal::currentUser();
      $role = $user->getRoles();

      $anonymous = 'anonymous';
      $administrator = 'administrator';
      $senior_dev = 'senior_dev';
      $junior_dev = 'junior_dev';

      if ($role[0] == $anonymous || $role[0] == $junior_dev)
      {
        \Drupal::messenger()->addError('You have no permission to edit task');
        return;
      }
      else if ($role[1] == $administrator || $role[1] == $senior_dev)
      {

        // get Task id from URI
        $task_id = \Drupal::routeMatch()->getParameter('task_id');
        $form_state->setStorage([$task_id]);

        // get query about Task
        $task_connection = \Drupal::database()->query("SELECT * FROM {tasks} WHERE id = ".$task_id."");
        $results = $task_connection->fetchAssoc();

        //seniors names list /select//

        $dev_connection = \Drupal::database()->query("SELECT `uid`, `name`
        FROM {users_field_data}
        INNER JOIN {user__roles}
        ON {users_field_data}.uid = {user__roles}.entity_id
        WHERE {users_field_data}.uid > 0 AND {user__roles}.roles_target_id = 'senior_dev'");

        $senior_name_options = [];

        if ($dev_connection)
        {
          while ($senior_name_records = $dev_connection->fetchAssoc())
          {
            $senior_name_options[$senior_name_records['name']] = $senior_name_records['name'];
          }
        }

        $form['task_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Task name'),
          '#value' => $results['task_name'],
          '#required' => TRUE,
        ];

        $form['task_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('URL'),
          '#value' => $results['task_url'],
          '#required' => TRUE,
        ];

        $form['papa_name'] = [
          '#type' => 'select',
          '#options' => $senior_name_options,
          '#title' => $this->t('Name of supervisor'),
          '#value' => $results['papa_name'],
          '#required' => TRUE,
        ];

        $form['papa_evaluation'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Time evaluation of supervisor (hours)'),
          '#value' => $results['papa_evaluation'],
          '#required' => TRUE,
        ];

        $form['kinder_evaluation'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Time evaluation of junior dev (hours)'),
          '#value' => $results['kinder_evaluation'],
          '#required' => TRUE,
        ];


        $form['begin_time'] = [
          '#type' => 'date',
          '#date_format' => 'Y-m-d H:i',
          '#title' => $this->t('Task start time'),
          '#value' => $results['begin_time'],
          '#required' => TRUE,
          ];

        $form['done'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('finished'),
        ];

        $form['end_time'] = [
          '#type' => 'date',
          '#date_format' => 'Y-m-d H:i',
          '#title' => $this->t('Task end time'),
          '#value' => $results['end_time'],
          '#required' => TRUE,
        ];

        $form['duration'] = [
          '#type' => 'number',
          '#value' => $results['duration'],
          '#title' => $this->t('Hours junior dev required to finish task (only numeric)'),
          '#required' => TRUE,
        ];

        $form['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('submit')
        ];

        $storage = $form_state->getStorage();
        $task_id = $storage[0];

        return $form;
      }
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      // get task id from buildform
      $storage = $form_state->getStorage();
      $task_id = $storage[0];

      //finished checkbox yes/no

      if($form_state->getValue('done') == 1) {
        $done = 'yes';
      } else {
        $done = 'no';
      };

      //duration numeric

      // if(is_numeric($form_state->getValue('duration'))) {

      //   $duration = $form_state->getValue('duration');
      // } else {
      //   \Drupal::messenger()->addError('Duration must be numeric');
      //   return;
      // }

      /** @var \Drupal\Core\Database\Connection $connection */
      $connection = \Drupal::service('database');

      $connection->update('tasks')
      ->fields([
        'task_name' => $form_state->getValue('task_name'),
        'task_url' => $form_state->getValue('task_url'),
        'papa_name' => $form_state->getValue('papa_name'),
        'papa_evaluation' => $form_state->getValue('papa_evaluation'),
        'kinder_evaluation' => $form_state->getValue('kinder_evaluation'),
        'begin_time' => $form_state->getValue('begin_time'),
        'end_time' => $form_state->getValue('end_time'),
        'done' => $done,
        'duration' => $form_state->getValue('duration'),
      ])
      ->where('id = '.$task_id.'')
      ->execute();

      $path = '/work_assigments/drupal/baltic_amadeus/tasks-list';
      $response = new RedirectResponse($path);
      $response->send();
      \Drupal::messenger()->addStatus('Task updated');

      return;

    }
}

