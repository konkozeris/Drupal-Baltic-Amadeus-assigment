<?php
use Drupal\Core\Database\Database;

function chart() {

      $query = \Drupal::database()->select('tasks')
    ->fields('tasks', ['task_name', 'papa_evaluation', 'kinder_evaluation', 'duration'])
    ->execute();

    $json_query[] = ['Task name', 'Senior evaluation', 'Junior evaluation', 'Actual'];

    while($row = $query->fetchAssoc()) {

    $json_query[] = [
      $row['task_name'],
      floatval($row['papa_evaluation']),
      floatval($row['kinder_evaluation']),
      floatval($row['duration'])
    ];
   };

   echo json_encode($json_query);


  }
