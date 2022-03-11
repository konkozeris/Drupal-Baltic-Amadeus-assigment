<?php

namespace Drupal\task_chart\Controller;

use Drupal\Core\Controller\ControllerBase;

class TaskChartController extends ControllerBase {

  public function page()
  {
    $chart = [
      '#markup' => '<div id="columnchart_material" style="width: 800px; height: 500px;"></div>',
      ['#attached' => [
        'library' => [
          'task_chart/google_charts',
          'task_chart/task_chart',
          'task_chart/jquery'],
      ],
    ]];

    $query = \Drupal::database()->select('tasks')
    ->fields('tasks', ['task_name', 'papa_evaluation', 'kinder_evaluation', 'duration'])
    ->execute();

    $json_query[] = ['Task name', 'Senior evaluation', 'Junior evaluation', 'Actual'];

   while($row = $query->fetchAssoc()) {
    //  $json_query['task_name'] = $row['task_name'];
    //  $json_query['papa_evaluation'] = $row['papa_evaluation'];
    //  $json_query['kinder_evaluation'] = $row['kinder_evaluation'];
    //  $json_query['duration'] = $row['duration'];

     $json_query[] = [
       $row['task_name'],
       floatval($row['papa_evaluation']),
       floatval($row['kinder_evaluation']),
       floatval($row['duration'])
     ];
   };
  echo json_encode($json_query);

    return $chart;

  }
}
