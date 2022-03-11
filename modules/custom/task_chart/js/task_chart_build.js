
      google.charts.load('current', {'packages':['bar']});
      google.charts.setOnLoadCallback(drawChart, true);


      function drawChart() {

        var jsonData =
          $.ajax({
            url: "modules/custom/task_chart/task_chart_data.php",
            type: "get",
            dataType: "json",
            async: false,
            success: function(response) {
              console.log(response);
            },
            error: function(response) { alert(response)},
          });
        console.log(jsonData);

        var data = new google.visualization.arrayToDataTable(jsonData);
          // data.addColumn('string', 'Task name');
          // data.addColumn('number', 'Senior dev evaluation');
          // data.addColumn('number', 'Junior dev evaluation');
          // data.addColumn('number', 'Actual time');

          // jQuery.each(jsonData, function(i, jsonData) {

          //   var task_name = jsonData.task_name;
          //   var papa_evaluation = parseFloat($.trim(jsonData.papa_evaluation));
          //   var kinder_evaluation = parseFloat($.trim(jsonData.kinder_evaluation));
          //   var duration = parseFloat($.trim(jsonData.duration));

          //   data.addRows([[task_name, papa_evaluation, kinder_evaluation, duration]]);

          // });

        var options = {
          chart: {
            title: 'Tasks time statistics',
            subtitle: 'Junior devs time for tasks',
          }
        };

        var chart = new google.charts.Bar(document.getElementById('columnchart_material'));

        chart.draw(data, google.charts.Bar.convertOptions(options));

        console.log('added');

      }

