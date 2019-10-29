<?php
   $dbHost = "localhost";
   $dbDatabase = "election_results";
   $dbUser = "root";
   $dbPasswrod = ""; 
   
   // $dbHost = "localhost";
   // $dbDatabase ="robdoarc_results";
   // $dbUser = "robdoarc_results";
   // $dbPasswrod = "kkBvBKecBXUVccfW";
   
   $mysqli = mysqli_connect($dbHost, $dbUser,$dbPasswrod, $dbDatabase);
   
   // Check connection
   if (mysqli_connect_errno())
   {
     echo "Failed to connect to MySQL: " . mysqli_connect_error();
   }
   
   $result1 = mysqli_query($mysqli,"SELECT DISTINCT office_name FROM election_results ");
?>
<!DOCTYPE HTML>
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>MN Constitutional Races</title>
      <style type="text/css">
      </style>
   </head>
   <body>
      <script src="js/highcharts.js"></script>
      <script src="js/series-label.js"></script>
      <!-- <script src="js/exporting.js"></script> -->
      <script src="js/highcharts-more.js"></script>
      <script src="js/solid-gauge.js"></script>
      <link rel='stylesheet' type='text/css' href='css/style.css'/>
      <form id="form1" action="index.php" method="post">
         <select class="office_name" name="office_name" id="office_name" onchange="this.form.submit()">
         <?php
            while($row = mysqli_fetch_array($result1)){
                echo "<option>";
                echo $row['office_name'];
                echo "</option>";
            }
            ?>
         </select>
      </form>
      <script type="text/javascript">
         var office_name = "<?php echo $_POST['office_name'] ?>";
         var dd = document.getElementById('office_name');
         for (var i = 0; i < dd.options.length; i++) {
             if (dd.options[i].text === office_name) {
                 dd.selectedIndex = i;
                 break;
             }
         }
      </script>
      <div>
        <div id="container" class="container column"></div>
        <div class="outer">
            <div id="container-reporting" class="chart-container column"></div>
        </div>
      </div>
      <!-- <div class="outer">
        <div id="container-reporting" class="chart-container"></div>
      </div> -->
      <?php
         $office_name = isset($_POST['office_name']) ? $_POST['office_name'] : 'U.S. Senator';
         $sql = "SELECT * FROM election_results WHERE office_name ='".$office_name."'";
         $result = mysqli_query($mysqli, $sql);
         $retVal = array();
         
         echo "<div class='table-wrapper'> ";
         echo "<div class='row-heading'>";
         echo $office_name;
         echo "</div>";
         
         echo "<div class='table'>";
         echo "<div class='row header blue'>
               <div class='cell'>Party</div><div class='cell'>Candidate</div>
               <div class='cell'>Votes</div>
               <div class='cell'>Total</div>
               <div class='cell'>%</div>
               <div class='cell'>Reporting</div>
             </div> "; // row header

             $i = 0;
             while($row = mysqli_fetch_array($result))
             {
                 echo "<div class='row'>";
                 echo "<div class='cell' data-title='Party'>" . $row['party_id'] . "</div>";
                 echo "<div class='cell' data-title='Candidate'>" . $row['candidate_name'] . "</div>";
                 echo "<div class='cell' data-title='Votes'>" . $row['votes'] . "</div>";
                 echo "<div class='cell' data-title='Total'>" . $row['votes_total'] . "</div>";
                 echo "<div class='cell' data-title='%'>" . $row['votes_pct'] . "</div>";

                 $pctsReportingPercent=round($row['precincts_reporting']/$row['precincts_total']*100,1);
                 array_push($retVal, array(
                     'name' => $row['candidate_name'],
                     'y' => $row['votes'],
                     // 'precincts_reporting' => $row['precincts_reporting'],
                     'precincts_reporting' => $pctsReportingPercent,
                     'precincts_total' => $row['precincts_total'],
                     'party_id' => $row['party_id']
                 ));
         
                 echo "<div class='cell'>" . $pctsReporting="{$row['precincts_reporting']}/{$row['precincts_total']} ($pctsReportingPercent%)" . "</div>";
                 echo "</div>"; // row

                 $i++;
             } 
             echo "</div>"; //table
             echo "</div>"; // table-wrapper
             echo "</div>";
         ?>
      <script type="text/javascript">
        var retVal = <?php echo json_encode($retVal); ?>;
        var chartData = [];
        var precincts_total = [];
        var precincts_reporting = [];

         for (var i in retVal) {
            chartData[i] = {
                name: retVal[i].name,
                y: parseFloat(retVal[i].y)
                color: Highcharts.getOptions().colors[i]
            }
                  
            precincts_total[i] = {
                min:0,
                max: parseFloat(retVal[i].precincts_total)
            }
            precincts_reporting[i] = {
                data: parseFloat(retVal[i].precincts_reporting)
            }
         }

        // for (var i in retVal) {
        //      var tmp = retVal[i].party_id;
        //      console.log(tmp);
        //      switch(tmp){
        //         case 'R':
        //           chartData[i].color = '#ff0000';
        //           i++;
        //           break;
        //         case 'D':
        //           chartData[i].color = '#0000ff';
        //           i++;
        //           break;
        //         case 'LIB':
        //           chartData[i].color = '#ffff00';
        //           i++;
        //           break;
        //         case 'MGP':
        //           chartData[i].color = '#008000';
        //           i++;
        //           break;
        //         default:
        //           chartData[i].color = '#7300e6';
        //           i++;
        //      }
        //  }

         Highcharts.chart('container', {
             title: {
                 text: 'MN Constitutional Races'
             },
             chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
             },
             tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.2f}%</b>'
             },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            credits: {
                enabled: false
            },
            series: [{
                 colorByPoint: true,
                 name: 'Votes',
                 data: chartData,
                 center: ['50%', '60%'],
                 size: '100%',
                 dataLabels: {
                     enabled: false
                 }
            }]
         });
        var gaugeOptions = {

            chart: {
                type: 'solidgauge'
            },

            title: null,

            pane: {
                center: ['50%', '85%'],    //position
                size: '100%',              //size
                startAngle: -90,
                endAngle: 90,
                background: {
                    backgroundColor:
                        Highcharts.defaultOptions.legend.backgroundColor || '#EEE',
                    innerRadius: '60%',
                    outerRadius: '80%',
                    shape: 'arc'
                }
            },

            tooltip: {
                enabled: false
            },

            // the value axis
            yAxis: {
                stops: [
                    [0.1, '#55BF3B'], // green
                    [0.5, '#DDDF0D'], // yellow
                    [0.9, '#DF5353'] // red
                ],
                lineWidth: 0,
                minorTickInterval: null,
                tickAmount: 2,
                title: {
                    y: -70
                },
                labels: {
                    y: 16
                }
            },
            responsive: {
              rules: [{
                condition: {
                  maxWidth: 300
                },
                chartOptions: {
                  chart: {
                    className: 'small-chart'
                  }
                }
              }]
            },
            plotOptions: {
                solidgauge: {
                    dataLabels: {
                        y: 5,
                        borderWidth: 0,
                        useHTML: true
                    }
                }
            }
        };

        // The Reporting gauge
        var chartSpeed = Highcharts.chart('container-reporting', Highcharts.merge(gaugeOptions, {
            yAxis: precincts_total,
            credits: {
                enabled: false
            },
            series: [{
                data: [ precincts_reporting[0].data ],
                dataLabels: {
                    format:
                        '<div style="text-align:center">' +
                        '<span style="font-size:25px">{y}</span><br/>' +
                        '</div>'
                }
            }]
        }));
      </script>
  </body>
</html>

