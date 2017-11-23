<?php
	$intervals = array();

	$intervals['months'] = array(
		'1' => __( 'January', 'dokan-wc-booking' ),
		'2' => __( 'February', 'dokan-wc-booking' ),
		'3' => __( 'March', 'dokan-wc-booking' ),
		'4' => __( 'April', 'dokan-wc-booking' ),
		'5' => __( 'May', 'dokan-wc-booking' ),
		'6' => __( 'June', 'dokan-wc-booking' ),
		'7' => __( 'July', 'dokan-wc-booking' ),
		'8' => __( 'August', 'dokan-wc-booking' ),
		'9' => __( 'September', 'dokan-wc-booking' ),
		'10' => __( 'October', 'dokan-wc-booking' ),
		'11' => __( 'November', 'dokan-wc-booking' ),
		'12' => __( 'December', 'dokan-wc-booking' )
	);

	$intervals['days'] = array(
		'1' => __( 'Monday', 'dokan-wc-booking' ),
		'2' => __( 'Tuesday', 'dokan-wc-booking' ),
		'3' => __( 'Wednesday', 'dokan-wc-booking' ),
		'4' => __( 'Thursday', 'dokan-wc-booking' ),
		'5' => __( 'Friday', 'dokan-wc-booking' ),
		'6' => __( 'Saturday', 'dokan-wc-booking' ),
		'7' => __( 'Sunday', 'dokan-wc-booking' )
	);

	for ( $i = 1; $i <= 52; $i ++ ) {
		$intervals['weeks'][ $i ] = sprintf( __( 'Week %s', 'dokan-wc-booking' ), $i );
	}

	if ( ! isset( $pricing['type'] ) ) {
		$pricing['type'] = 'custom';
	}
	if ( ! isset( $pricing['modifier'] ) ) {
		$pricing['modifier'] = '';
	}
	if ( ! isset( $pricing['base_modifier'] ) ) {
		$pricing['base_modifier'] = '';
	}
	if ( ! isset( $pricing['base_cost'] ) ) {
		$pricing['base_cost'] = '';
	}
?>
<tr>
	<td class="sort">&nbsp;</td>
	<td>
		<div class="select wc_booking_pricing_type">
			<select name="wc_booking_pricing_type[]">
				<option value="custom" <?php selected( $pricing['type'], 'custom' ); ?>><?php _e( 'Date range', 'dokan-wc-booking' ); ?></option>
				<option value="months" <?php selected( $pricing['type'], 'months' ); ?>><?php _e( 'Range of months', 'dokan-wc-booking' ); ?></option>
				<option value="weeks" <?php selected( $pricing['type'], 'weeks' ); ?>><?php _e( 'Range of weeks', 'dokan-wc-booking' ); ?></option>
				<option value="days" <?php selected( $pricing['type'], 'days' ); ?>><?php _e( 'Range of days', 'dokan-wc-booking' ); ?></option>
				<option value="time" <?php selected( $pricing['type'], 'time' ); ?>><?php _e( 'Time Range', 'dokan-wc-booking' ); ?></option>
				<option value="persons" <?php selected( $pricing['type'], 'persons' ); ?>><?php _e( 'Person count', 'dokan-wc-booking' ); ?></option>
				<option value="blocks" <?php selected( $pricing['type'], 'blocks' ); ?>><?php _e( 'Block count', 'dokan-wc-booking' ); ?></option>
				<optgroup label="<?php _e( 'Time Ranges', 'dokan-wc-booking' ); ?>">
					<option value="time" <?php selected( $pricing['type'], 'time' ); ?>><?php _e( 'Time Range (all week)', 'dokan-wc-booking' ); ?></option>
					<option value="time:range" <?php selected( $pricing['type'], 'time:range' ); ?>><?php _e( 'Date Range with time', 'dokan-wc-booking' ); ?></option>
					<?php foreach ( $intervals['days'] as $key => $label ) : ?>
						<option value="time:<?php echo $key; ?>" <?php selected( $pricing['type'], 'time:' . $key ) ?>><?php echo $label; ?></option>
					<?php endforeach; ?>
				</optgroup>
			</select>
		</div>
	</td>
	<td style="border-right:0;">
	<div class="bookings-datetime-select-from">
		<div class="select from_day_of_week">
			<select name="wc_booking_pricing_from_day_of_week[]">
				<?php foreach ( $intervals['days'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['from'] ) && $pricing['from'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select from_month">
			<select name="wc_booking_pricing_from_month[]">
				<?php foreach ( $intervals['months'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['from'] ) && $pricing['from'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select from_week">
			<select name="wc_booking_pricing_from_week[]">
				<?php foreach ( $intervals['weeks'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['from'] ) && $pricing['from'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="from_date">
			<?php
			$from_date = '';
			if ( 'custom' === $pricing['type'] && ! empty( $pricing['from'] ) ) {
				$from_date = $pricing['from'];
			} else if ( 'time:range' === $pricing['type'] && ! empty( $pricing['from_date'] ) ) {
				$from_date = $pricing['from_date'];
			}
			?>
			<input type="text" class="date-picker" name="wc_booking_pricing_from_date[]" value="<?php echo esc_attr( $from_date ); ?>" />
		</div>

		<div class="from_time">
			<input type="time" class="time-picker" name="wc_booking_pricing_from_time[]" value="<?php if ( strrpos( $pricing['type'], 'time' ) === 0 && ! empty( $pricing['from'] ) ) echo $pricing['from'] ?>" placeholder="HH:MM" />
		</div>

		<div class="from">
			<input type="number" step="1" name="wc_booking_pricing_from[]" value="<?php if ( ! empty( $pricing['from'] ) && is_numeric( $pricing['from'] ) ) echo $pricing['from'] ?>" />
		</div>
	</div>
	</td>
	<td style="border-right:0;" width="25px;" class="bookings-to-label-row">
		<p><?php _e( 'to', 'dokan-wc-booking' ); ?></p>
		<p class="bookings-datetimerange-second-label"><?php _e( 'to', 'dokan-wc-booking' ); ?></p>
	</td>
	<td>
	<div class="bookings-datetime-select-to">
		<div class="select to_day_of_week">
			<select name="wc_booking_pricing_to_day_of_week[]">
				<?php foreach ( $intervals['days'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select to_month">
			<select name="wc_booking_pricing_to_month[]">
				<?php foreach ( $intervals['months'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select to_week">
			<select name="wc_booking_pricing_to_week[]">
				<?php foreach ( $intervals['weeks'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="to_date">
			<?php
			$to_date = '';
			if ( 'custom' === $pricing['type'] && ! empty( $pricing['to'] ) ) {
				$to_date = $pricing['to'];
			} else if ( 'time:range' === $pricing['type'] && ! empty( $pricing['to_date'] ) ) {
				$to_date = $pricing['to_date'];
			}
			?>
			<input type="text" class="date-picker" name="wc_booking_pricing_to_date[]" value="<?php echo esc_attr( $to_date ); ?>" />
		</div>

		<div class="to_time">
			<input type="time" class="time-picker" name="wc_booking_pricing_to_time[]" value="<?php if ( strrpos( $pricing['type'], 'time' ) === 0 && ! empty( $pricing['to'] ) ) echo $pricing['to']; ?>" placeholder="HH:MM" />
		</div>

		<div class="to">
			<input type="number" step="1" min="0" name="wc_booking_pricing_to[]" value="<?php if ( ! empty( $pricing['to'] ) && is_numeric( $pricing['to'] ) ) echo $pricing['to'] ?>" />
		</div>
	</div>
	</td>
	<td>
		<div class="select">
			<select name="wc_booking_pricing_base_cost_modifier[]">
				<option <?php selected( $pricing['base_modifier'], '' ); ?> value="">+</option>
				<option <?php selected( $pricing['base_modifier'], 'minus' ); ?> value="minus">-</option>
				<option <?php selected( $pricing['base_modifier'], 'times' ); ?> value="times">&times;</option>
				<option <?php selected( $pricing['base_modifier'], 'divide' ); ?> value="divide">&divide;</option>
			</select>
		</div>
            <input class="dokan-form-control" type="number" step="0.01" name="wc_booking_pricing_base_cost[]" min="0" value="<?php if ( ! empty( $pricing['base_cost'] ) ) echo $pricing['base_cost']; ?>" placeholder="0" />
        <?php do_action( 'woocommerce_bookings_after_booking_pricing_base_cost', $pricing, $post_id ); ?>
	</td>
	<td>
		<div class="select">
			<select name="wc_booking_pricing_cost_modifier[]">
				<option <?php selected( $pricing['modifier'], '' ); ?> value="">+</option>
				<option <?php selected( $pricing['modifier'], 'minus' ); ?> value="minus">-</option>
				<option <?php selected( $pricing['modifier'], 'times' ); ?> value="times">&times;</option>
				<option <?php selected( $pricing['modifier'], 'divide' ); ?> value="divide">&divide;</option>
			</select>
		</div>
            <input class="dokan-form-control" type="number" step="0.01" name="wc_booking_pricing_cost[]" min="0" value="<?php if ( ! empty( $pricing['cost'] ) ) echo $pricing['cost']; ?>" placeholder="0" />
        <?php do_action( 'woocommerce_bookings_after_booking_pricing_cost', $pricing, $post_id ); ?>
	</td>
	<td class="remove">&nbsp;</td>
</tr>