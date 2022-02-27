<?php

namespace Drupal\input_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class InputForm extends FormBase
{
    public function getFormId()
    {
      return 'input_form';
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

      if ($role[0] == $anonymous || $role[0] == $junior_dev)
      {
        \Drupal::messenger()->addError('You have no permission to create task');
        return;
      }
      else if ($role[1] == $administrator || $role[1] == $senior_dev)
      {
        $form['task_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Task name'),
          '#required' => TRUE,
        ];

        $form['task_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('URL'),
          '#required' => TRUE,
        ];

        //seniors names list /select//
        $connection = \Drupal::database();

        $sql = "SELECT `uid`, `name`
        FROM {users_field_data}
        INNER JOIN {user__roles}
        ON {users_field_data}.uid = {user__roles}.entity_id
        WHERE {users_field_data}.uid > 0 AND {user__roles}.roles_target_id = 'senior_dev'";

        $senior_name_query = $connection->query($sql);
        $senior_name_options = array();

        if ($senior_name_query) {

          while ($senior_name_records = $senior_name_query->fetchAssoc())
          {
            $senior_name_options[$senior_name_records['name']] = $senior_name_records['name'];
          }
        }

        $form['papa_name'] = [
          '#type' => 'select',
          '#options' => $senior_name_options,
          '#title' => $this->t('Name of supervisor'),
          '#required' => TRUE,
        ];

        $form['papa_evaluation'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Time evaluation of supervisor (hours)'),
          '#required' => TRUE,
        ];

        $form['kinder_evaluation'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Time evaluation of junior dev (hours)'),
          '#required' => TRUE,
        ];

        $form['begin_time'] = [
          '#type' => 'date',
          '#date_format' => 'Y-m-d H:i',
          '#title' => $this->t('Task start time'),
          '#required' => TRUE,
          ];

        $form['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('submit')
        ];

        return $form;
      }
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
      $done = $form_state->getValue('done');

      if(strlen($done) > 3 || is_numeric($done)) {
        $form_state->setErrorByName('done', $this->t('MUST BE YES OR NO'));
      };
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      /** @var \Drupal\Core\Database\Connection $connection */
      $connection = \Drupal::service('database');

      $connection->insert('tasks')
      ->fields([
        'task_name' => $form_state->getValue('task_name'),
        'task_url' => $form_state->getValue('task_url'),
        'papa_name' => $form_state->getValue('papa_name'),
        'papa_evaluation' => $form_state->getValue('papa_evaluation'),
        'kinder_evaluation' => $form_state->getValue('kinder_evaluation'),
        'begin_time' => $form_state->getValue('begin_time'),
        'end_time' => 'default date',
        'done'=> 'no',
        'created_timestamp' => date('Y-m-d H:i:s'),
      ])
      ->execute();

     \Drupal::messenger()->addStatus('Task created');
    }
}

