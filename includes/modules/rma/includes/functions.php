<?php

/**
 * Warranty Type
 *
 * @since 1.0.0
 *
 * @return array|string
 */
function dokan_rma_warranty_type( $type = '' ) {
    $warranty_type = apply_filters( 'dokan_rma_warranty_type', [
        'no_warranty'       => __( 'No Warranty', 'dokan' ),
        'included_warranty' => __( 'Warranty Included', 'dokan' ),
        'addon_warranty'    => __( 'Warranty as Add-On', 'dokan' )
    ] );

    if ( ! empty( $type ) ) {
        return isset( $warranty_type[$type] ) ? $warranty_type[$type] : '';
    }

    return $warranty_type;
}

/**
 * Warranty Length if included warranty
 *
 * @since 1.0.0
 *
 * @return string | Array
 */
function dokan_rma_warranty_length( $length = '' ) {
    $warranty_length = apply_filters( 'dokan_rma_warranty_length', [
        'limited'  => __( 'Limited', 'dokan' ),
        'lifetime' => __( 'Lifetime', 'dokan' )
    ] );

    if ( ! empty( $length ) ) {
        return isset( $warranty_length[$length] ) ? $warranty_length[$length] : '';
    }

    return $warranty_length;
}

/**
 * Warranty Length duration if included warranty
 *
 * @since 1.0.0
 *
 * @return string | Array
 */
function dokan_rma_warranty_length_duration( $duration = '' ) {
    $warranty_length_duration = [
        'days'   => __( 'Days', 'dokan' ),
        'weeks'  => __( 'Weeks', 'dokan' ),
        'months' => __( 'Months', 'dokan' ),
        'years'  => __( 'Years', 'dokan' )
    ];

    if ( ! empty( $duration ) ) {
        return isset( $warranty_length_duration[$duration] ) ? $warranty_length_duration[$duration] : '';
    }

    return $warranty_length_duration;
}

/**
 * Get refund Reasons formatted
 *
 * @since 1.0.0
 *
 * @return void
 */
function dokan_rma_refund_reasons( $reason = '' ) {
    $reasons = dokan_get_option( 'rma_reasons', 'dokan_rma', [] );

    if ( ! empty( $reasons ) ) {
        $reasons = wp_list_pluck( $reasons, 'value', 'id' );
    }

    if ( $reason ) {
        return isset( $reasons[$reason] ) ? $reasons[$reason] : '';
    }

    return $reasons;
}

/**
 * Get duration value
 *
 * @since 1.0.0
 *
 * @return void
 */
function dokan_rma_get_duration_value( $duration, $value = 0 ) {
    $unit = dokan_rma_warranty_length_duration( $duration );

    if ( 1 == $value ) {
        $unit = rtrim( $unit, 's' );
    }

    return $unit;
}
