<?php
/** @var array $logs */

use DevLog\DataMapper\Models\Log;

$labels        = array();
$diff          = array();
$colors        = array();
$border_colors = array();
$callback_diff = array();

/** @var Log $log */
$messages = $log->getMessageList()->getList();

$start = null;

foreach ( $messages as $key => $message ) {
	if ( $message->getType() === 'S' && $messages[ $key + 1 ]->getType() === 'C' ) {
		if ( ! empty( $callback_diff ) ) {
			foreach ( $callback_diff as $index => $item ) {
				if ( in_array( $message->getMessage(), $item ) && in_array( $messages[ $key + 1 ]->getMessage(), $item ) ) {
					$callback_diff[ $index ][2] += $messages[ $key + 1 ]->getTime() - $message->getTime();
					$callback_diff[ $index ][3] += 1;
					continue 2;
				}
			}
		}
		$start           = $message->getMessage();
		$callback_diff[] = array(
			$start,
			$messages[ $key + 1 ]->getMessage(),
			$messages[ $key + 1 ]->getTime() - $message->getTime()
		);
		continue;
	}


	if ( $message->getType() === 'C' && $messages[ $key + 1 ]->getType() === 'C' ) {
		if ( ! empty( $callback_diff ) ) {
			foreach ( $callback_diff as $index => $item ) {
				if ( in_array( $message->getMessage(), $item ) && in_array( $messages[ $key + 1 ]->getMessage(), $item ) ) {
					$callback_diff[ $index ][2] += $messages[ $key + 1 ]->getTime() - $message->getTime();
					$callback_diff[ $index ][3] += 1;
					continue 2;
				}
			}
		}
		$callback_diff[] = array(
			$start,
			$message->getMessage(),
			$messages[ $key + 1 ]->getTime() - $message->getTime()
		);
		continue;
	}

	if ( $message->getType() === 'E' && $messages[ $key - 1 ]->getType() === 'C' ) {
		if ( ! empty( $callback_diff ) ) {
			foreach ( $callback_diff as $index => $item ) {
				if ( in_array( $message->getMessage(), $item ) && in_array( $messages[ $key - 1 ]->getMessage(), $item ) ) {
					$callback_diff[ $index ][2] += $message->getTime() - $messages[ $key - 1 ]->getTime();
					$callback_diff[ $index ][3] += 1;
					continue 2;
				}
			}
		}
		$callback_diff[] = array(
			$start,
			$messages[ $key - 1 ]->getMessage(),
			$message->getTime() - $messages[ $key - 1 ]->getTime()
		);
		continue;
	}
}


foreach ( $messages as $key => $message ) {
	if ( $message->getType() === 'E' ) {
//		if ( $message->getCategory() < 0.001 ) {
//			continue;
//		}
		$labels[]        = $message->getMessage();
		$diff[]          = number_format( $message->getCategory(), 7 );
		$num             = mt_rand( 0, 255 );
		$colors[]        = getColor( $num );
		$border_colors[] = getBorderColor( $num );
	}
}


function getColor( $num ) {
	$hash = md5( 'color' . $num ); // modify 'color' to get a different palette

	return 'rgba( ' . hexdec( substr( $hash, 0, 2 ) ) . ', ' . hexdec( substr( $hash, 2, 2 ) ) . ', ' . hexdec( substr( $hash, 4, 2 ) ) . ', 0.2 )';

}

function getBorderColor( $num ) {
	$hash = md5( 'color' . $num ); // modify 'color' to get a different palette

	return 'rgba( ' . hexdec( substr( $hash, 0, 2 ) ) . ', ' . hexdec( substr( $hash, 2, 2 ) ) . ', ' . hexdec( substr( $hash, 4, 2 ) ) . ', 1 )';

}

?>

<style>

    .ggl-tooltip {
        border: 1px solid #E0E0E0;
        font-family: Arial, Helvetica;
        font-size: 10pt;
        padding: 12px 12px 12px 12px;
    }

    .ggl-tooltip div {
        padding: 6px 6px 6px 6px;
    }

    .ggl-tooltip span {
        font-weight: bold;
    }

</style>

<script type="application/javascript">

    var filter_diff = <?php echo json_encode( $diff ); ?>;
    var labels = <?php echo json_encode( $labels );?>;
    var callback_diff = <?php echo json_encode( $callback_diff );?>;


    var ctx = document.getElementById('WPPFChart');
    var WPPFChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Filters Diff',
                data: filter_diff,
                backgroundColor: <?php echo json_encode( $colors );?>,
                borderColor: <?php echo json_encode( $border_colors );?>,
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    google.charts.load('current', {'packages': ['timeline']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var container = document.getElementById('timeline');
        var chart = new google.visualization.Timeline(container);
        var dataTable = new google.visualization.DataTable();

        dataTable.addColumn({type: 'string', id: 'Hooks'});
        dataTable.addColumn({type: 'string', id: 'Name'});
        dataTable.addColumn({type: 'string', role: 'tooltip','p': {'html': true} });
        dataTable.addColumn({type: 'number', id: 'Start'});
        dataTable.addColumn({type: 'number', id: 'End'});

        var rows = [];
        var tmp = 0;
        filter_diff.forEach(function (filter, index) {
            if (index === 0) {
                rows.push(['Start - End', labels[index],'', 0, parseFloat(filter)]);
                tmp += parseFloat(filter);
            } else {
                rows.push(['Start - End', labels[index],'', tmp, tmp + parseFloat(filter)]);
                tmp += parseFloat(filter);
            }
        });

        dataTable.addRows(rows);


        for (var i = 0; i < dataTable.getNumberOfRows(); i++) {
            var duration = dataTable.getValue(i, 4) - dataTable.getValue(i, 3);

            var tooltip =
                '<div class="ggl-tooltip">' +
                    '<span>' +
                        dataTable.getValue(i, 1) + '' +
                    '</span>' +
                '</div>' +
                '<div class="ggl-tooltip">' +
                    '<span>' +
                        dataTable.getValue(i, 0) +
                    '</span>: ' +
                    dataTable.getValue(i, 3) + ' - ' +
                    dataTable.getValue(i, 4) +
                '</div>' +
                '<div class="ggl-tooltip"><span>Duration: </span>' + duration + 's </div>';

            dataTable.setValue(i, 2, tooltip);
        }

        chart.draw(dataTable, {
            tooltip: {isHtml: true},
            legend: 'none'
        });
    }

    ctx.addEventListener('click', function (e) {
        var activeElement = WPPFChart.getElementAtEvent(e);
        var index = activeElement[0]['_index'];
        console.log(labels[index])
        console.log(callback_diff)
    });


</script>


