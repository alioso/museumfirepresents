<div style="text-align: right">
	<select class="cuepro-insights-range">
		<option value="<?php echo esc_url( $screen_url ); ?>"><?php esc_html_e( 'Last 7 Days', 'cuepro' ); ?></option>
		<option value="<?php echo esc_url( add_query_arg( 'range', 30, $screen_url ) ); ?>"<?php selected( $interval, 30 ); ?>><?php esc_html_e( 'Last 30 Days', 'cuepro' ); ?></option>
	</select>
</div>
<script>
jQuery(function( $ ) {
	$( '.cuepro-insights-range' ).on( 'change', function() {
		window.location = this.value;
	});
});
</script>


<div class="cuepro-insights-section">
	<div class="cuepro-metrics">
		<dl class="cuepro-metric">
			<dt class="cuepro-metric-label"><?php esc_html_e( 'Plays', 'cuepro' ); ?></dt>
			<dd class="cuepro-metric-value"><?php echo absint( $metrics->get_play_count() ); ?></dd>
		</dl>

		<dl class="cuepro-metric">
			<dt class="cuepro-metric-label"><?php esc_html_e( 'Listeners', 'cuepro' ); ?></dt>
			<dd class="cuepro-metric-value"><?php echo absint( $metrics->get_listener_count() ); ?></dd>
		</dl>

		<dl class="cuepro-metric">
			<dt class="cuepro-metric-label"><?php esc_html_e( 'Tracks', 'cuepro' ); ?></dt>
			<dd class="cuepro-metric-value"><?php echo absint( $metrics->get_track_count() ) ?></dd>
		</dl>
	</div>
</div>


<div class="cuepro-insights-section">
	<h2 class="cuepro-insights-section-title"><?php esc_html_e( 'Plays per Day', 'cuepro' ); ?></h2>

	<div class="cuepro-chart">
		<div><canvas id="cuepro-plays-per-day" width="800" height="230"></canvas></div>
	</div>

	<?php
	$results = $metrics->get_play_count_by_day();

	$chart_data = array(
		'labels'   => array_keys( $results ),
		'datasets' => array(
			array(
				'label'       => esc_html( 'Plays Per Day', 'cuepro' ),
				'fillColor'   => 'rgba(22, 126, 217, 0.2)',
				'strokeColor' => 'rgba(22, 126, 217, 0)',
				'data'        => array_map( 'absint', wp_list_pluck( $results, 'play_count' ) ),
			),
		),
	);
	?>
	<script type="text/javascript">
	jQuery(function( $ ) {
		var data = <?php echo wp_json_encode( $chart_data ); ?>;
		var ctx = document.getElementById( 'cuepro-plays-per-day' ).getContext( '2d' );
		var chart = new Chart( ctx ).Line( data, {
			animation: false,
			bezierCurve: false,
			datasetStroke: false,
			datasetStrokeWidth: 0,
			pointDot: false,
			responsive: true,
			//maintainAspectRatio: false,
			scaleShowGridLines: true,
			scaleLineColor: 'rgba(0, 0, 0, 0.1)',
			scaleLineWidth: 1,
			scaleFontFamily: 'sans-serif'
		});
	});
	</script>
</div>


<div class="cuepro-insights-section">
	<h2 class="cuepro-insights-section-title"><?php esc_html_e( 'Plays per Track', 'cuepro' ); ?></h2>

	<table class="cuepro-table widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Track', 'cuepro' ); ?></th>
				<th class="cuepro-numeric-column"><?php esc_html_e( 'Plays', 'cuepro' ); ?></th>
				<th><?php esc_html_e( 'Complete/Partial/Skip', 'cuepro' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$results    = $metrics->get_play_count_per_track();
			$bar_width  = 200;
			$bar_height = 10;

			if ( $results ) :
				?>
				<?php foreach ( $results as $item ) : ?>
					<tr>
						<td><?php $this->print_target_title( $item ); ?></td>
						<td class="cuepro-numeric-column"><?php echo absint( $item->play_count ); ?></td>
						<td>
							<svg width="<?php echo $bar_width; ?>" height="<?php echo $bar_height; ?>">
								<g>
									<?php
									printf(
										'<rect class="cuepro-complete" width="%f" height="%d"><title>%s%%</title></rect>',
										$item->complete_rate * $bar_width,
										$bar_height,
										number_format( $item->complete_rate * 100, 1 )
									);

									printf(
										'<rect class="cuepro-partial" x="%f" width="%f" height="%d"><title>%s%%</title></rect>',
										$item->complete_rate * $bar_width,
										$item->partial_rate * $bar_width,
										$bar_height,
										number_format( $item->partial_rate * 100, 1 )
									);

									printf(
										'<rect class="cuepro-skipped" x="%f" width="%f" height="%d">
											<title>%s%%</title>
										</rect>',
										( $item->complete_rate + $item->partial_rate ) * $bar_width,
										$item->skip_rate * $bar_width,
										$bar_height,
										number_format( $item->skip_rate * 100, 1 )
									);
									?>
								</g>
							</svg>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="3"><em><?php esc_html_e( 'No data has been collected yet.', 'cuepro' ); ?></em></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>


<div class="cuepro-insights-section">
	<h2 class="cuepro-insights-section-title"><?php esc_html_e( 'Chart', 'cuepro' ); ?></h2>

	<table class="cuepro-table widefat">
		<thead>
			<tr>
				<th class="cuepro-numeric-column"><?php esc_html_e( '#', 'cuepro' ); ?></th>
				<th><?php esc_html_e( 'Track', 'cuepro' ); ?></th>
				<th style="text-align: right"><?php esc_html_e( 'Change', 'cuepro' ); ?></th>
				<th class="cuepro-numeric-column"><?php esc_html_e( 'This Period', 'cuepro' ); ?></th>
				<th class="cuepro-numeric-column"><?php esc_html_e( 'Last Period', 'cuepro' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php $results = $metrics->get_chart_data(); ?>
			<?php if ( $results ) : ?>
				<?php foreach ( $results as $i => $result ) : ?>
					<?php
					if ( $result->plays_last_period ) {
						$delta = ( $result->plays_this_period - $result->plays_last_period ) / $result->plays_last_period;
						$delta_class = $delta > 0 ? 'cuepro-rising' : 'cuepro-falling';
						$delta = round( $delta * 100 ) . '%';
					} elseif ( $result->plays_this_period ) {
						$delta = 'New';
						$delta_class = 'cuepro-new';
					}
					?>
					<tr>
						<td class="cuepro-numeric-column"><?php echo absint( $i + 1 ); ?></td>
						<td><?php $this->print_target_title( $result ); ?></td>
						<td class="<?php echo $delta_class; ?>" style="text-align: right"><i class="dashicons"></i><?php echo esc_html( $delta ); ?></td>
						<td class="cuepro-numeric-column"><?php echo absint( $result->plays_this_period ); ?></td>
						<td class="cuepro-numeric-column"><?php echo absint( $result->plays_last_period ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="5"><em><?php esc_html_e( 'No data has been collected yet.', 'cuepro' ); ?></em></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>


<div class="cuepro-insights-section">
	<h2 class="cuepro-insights-section-title"><?php esc_html_e( 'Plays per Page', 'cuepro' ); ?></h2>

	<table class="cuepro-table widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Page', 'cuepro' ); ?></th>
				<th class="cuepro-numeric-column"><?php esc_html_e( 'Plays', 'cuepro' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php $results = $metrics->get_play_count_per_page(); ?>
			<?php if ( $results ) : ?>
				<?php foreach ( $results as $item ) : ?>
					<tr>
						<td><?php echo esc_html( $item->page_title ); ?></td>
						<td class="cuepro-numeric-column"><?php echo absint( $item->play_count ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="2"><em><?php esc_html_e( 'No data has been collected yet.', 'cuepro' ); ?></em></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
