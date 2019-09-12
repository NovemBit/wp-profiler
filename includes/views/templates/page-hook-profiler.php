<?php
/** @var array $logs */

use DevLog\DataMapper\Models\Log;

$labels        = array();
$diff          = array();
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
		if ( $message->getCategory() < 0.001 ) {
			continue;
		}
		$labels[]        = $message->getMessage();
		$diff[]          = number_format( $message->getCategory(), 7 );
		$num             = mt_rand( 0, 255 );
		$border_colors[] = getBorderColor( $num );
	}
}

function getBorderColor( $num ) {
	$hash = md5( 'color' . $num ); // modify 'color' to get a different palette

	return 'rgba( ' . hexdec( substr( $hash, 0, 2 ) ) . ', ' . hexdec( substr( $hash, 2, 2 ) ) . ', ' . hexdec( substr( $hash, 4, 2 ) ) . ', 1 )';

}

?>

<script type="application/javascript">

    var filter_diff = <?php echo json_encode( $diff ); ?>;
    var labels = <?php echo json_encode( $labels );?>;
    var callback_diff = <?php echo json_encode( $callback_diff );?>;


//region Filters chart
    Chart.defaults.timeline = Chart.defaults.horizontalBar;
    Chart.controllers.timeline = Chart.controllers.horizontalBar.extend({
        initialize: function() {
            return Chart.controllers.bar.prototype.initialize.apply(this, arguments);
        }
    });

    Chart.pluginService.register({
        beforeInit: function(chart) {
            if (chart.config.type === 'timeline') {
                var config = chart.config;

                var min = 0;
                var max = 0;
                filter_diff.forEach( function( filter ){
                    max += parseFloat(filter);
                } );

                config.options.scales.xAxes[0].ticks.min = min;
                config.options.scales.xAxes[0].ticks.max = max;

                var whiteDiff = [];

                filter_diff.forEach(function(e,i) {
                    if( i == 0 ){
                        whiteDiff.push(0);
                    }else {
                        whiteDiff.push(parseFloat(filter_diff[i-1]) + whiteDiff[i-1]);
                    }

                });

                config.data.datasets.unshift({
                    label: 'With Timeline',
                    backgroundColor: 'rgba(0, 0, 0, 0)',
                    data: whiteDiff
                });
            }
        }
    });

    var config = {
        type: 'timeline',
        data: {
            labels: labels,
            datasets: [{
                label: 'Filters Diff',
                backgroundColor: <?php echo json_encode( $border_colors );?>,
                data: filter_diff
            }]
        },
        options: {
            scales: {
                xAxes: [{
                    stacked: true
                }],
                yAxes: [{
                    stacked: true,
                    categoryPercentage: 0.5,
                    barPercentage: 1
                }]
            },
            tooltips:{
                enabled: true,
                mode: 'single',
                callbacks: {
                    label: function(tooltipItems, data) {
                        // return tooltipItems.yLabel + ' : ' + tooltipItems.xLabel + " Files";
                        return tooltipItems.xLabel;
                    }
                }
            }
        }
    };

    var ctx = document.getElementById("WPPFChart").getContext("2d");
    ctx.scale(2, 2);
    var WPPFChart = new Chart(ctx, config);

//endregion
        console.log( callback_diff )
    ctx.canvas.addEventListener('click', function (e) {
        var activeElement = WPPFChart.getElementAtEvent(e);
        var index = activeElement[0]['_index'];

        callback_diff.forEach(function( e, i ){
            if( e[0] == labels[index] ){
                console.log( e )
            }
        });

    });


</script>


