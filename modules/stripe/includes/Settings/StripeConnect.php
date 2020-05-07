<?php

defined( 'ABSPATH' ) || exit;

return apply_filters( 'dokan_get_stripe_connect_settings', [
    'enabled' => [
        'title'       => __( 'Enable/Disable', 'dokan' ),
        'label'       => __( 'Enable Stripe', 'dokan' ),
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'no',
    ],
    'title' => [
        'title'       => __( 'Title', 'dokan' ),
        'type'        => 'text',
        'description' => __( 'This controls the title which the user sees during checkout.', 'dokan' ),
        'default'     => __( 'Dokan Credit card (Stripe)', 'dokan' ),
        'desc_tip'    => true
    ],
    'description' => [
        'title'       => __( 'Description', 'dokan' ),
        'type'        => 'textarea',
        'description' => __( 'This controls the description which the user sees during checkout.', 'dokan' ),
        'default'     => 'Pay with your credit card via Stripe.',
        'desc_tip'    => true
    ],
    'allow_non_connected_sellers' => [
        'title'       => __( 'Non-connected sellers', 'dokan' ),
        'label'       => __( 'Allow ordering products from non-connected sellers', 'dokan' ),
        'type'        => 'checkbox',
        'description' => __( 'If this is enable, customers can order products from non-connected sellers. The payment will send to admin Stripe account.', 'dokan' ),
        'default'     => 'no',
        'desc_tip'    => true
    ],
    'enable_3d_secure' => [
        'title'       => __( '3D Secure and SCA', 'dokan' ),
        'label'       => __( 'Enable 3D Secure and Strong Customer Authentication', 'dokan' ),
        'type'        => 'checkbox',
        'description' => __( 'Note: 3D Secure and SCA ready transaction is only supported when both your platform and the connected account (Vendor) are in the same region: both in Europe or both in the U.S. Also automatic refund through stripe is not supported as well.', 'dokan' ),
        'default'     => 'no',
        'desc_tip'    => true
    ],
    'testmode' => [
        'title'       => __( 'Test mode', 'dokan' ),
        'label'       => __( 'Enable Test Mode', 'dokan' ),
        'type'        => 'checkbox',
        'description' => __( 'Place the payment gateway in test mode using test API keys.', 'dokan' ),
        'default'     => 'yes',
        'desc_tip'    => true
    ],
    'stripe_checkout' => [
        'title'       => __( 'Stripe Checkout', 'dokan' ),
        'label'       => __( 'Enable Stripe Checkout', 'dokan' ),
        'type'        => 'checkbox',
        'description' => __( '(This only works when 3D Secure and SCA is disabled) If enabled, this option shows a "pay" button and modal credit card form on the checkout, instead of credit card fields directly on the page.', 'dokan' ),
        'default'     => 'no',
        'desc_tip'    => true
    ],
    'stripe_checkout_locale' => [
        'title'       => __( 'Stripe Checkout locale', 'dokan' ),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'Language to display in Stripe Checkout modal. Specify Auto to display Checkout in the user\'s preferred language, if available. English will be used by default.', 'dokan' ),
        'default'     => 'en',
        'desc_tip'    => true,
        'options'     => [
            'auto' => __( 'Auto', 'dokan' ),
            'zh'   => __( 'Simplified Chinese', 'dokan' ),
            'da'   => __( 'Danish', 'dokan' ),
            'nl'   => __( 'Dutch', 'dokan' ),
            'en'   => __( 'English', 'dokan' ),
            'fi'   => __( 'Finnish', 'dokan' ),
            'fr'   => __( 'French', 'dokan' ),
            'de'   => __( 'German', 'dokan' ),
            'it'   => __( 'Italian', 'dokan' ),
            'ja'   => __( 'Japanese', 'dokan' ),
            'no'   => __( 'Norwegian', 'dokan' ),
            'es'   => __( 'Spanish', 'dokan' ),
            'sv'   => __( 'Swedish', 'dokan' ),
        ],
    ],
    'stripe_checkout_image' => [
        'title'       => __( 'Checkout Image', 'dokan' ),
        'description' => __( 'Optionally enter the URL to a 128x128px image of your brand or product. e.g. <code>https://yoursite.com/wp-content/uploads/2013/09/yourimage.jpg</code>', 'dokan' ),
        'type'        => 'text',
        'default'     => '',
        'desc_tip'    => true,
    ],
    'stripe_checkout_label' => [
        'title'       => __( 'Checkout Button Label', 'dokan' ),
        'description' => __( 'Optionally enter a Label for PAY button', 'dokan' ),
        'type'        => 'text',
        'default'     => '',
        'desc_tip'    => true,
    ],
    'saved_cards' => [
        'title'       => __( 'Saved cards', 'dokan' ),
        'label'       => __( 'Enable saved cards', 'dokan' ),
        'type'        => 'checkbox',
        'description' => __( 'If enabled, users will be able to pay with a saved card during checkout. Card details are saved on Stripe servers, not on your store.', 'dokan' ),
        'default'     => 'no',
        'desc_tip'    => true
    ],
    'live-credentials-title' => [
        'title' => __( 'Live credentials', 'dokan' ),
        'type'  => 'title',
    ],
    'secret_key' => [
        'title'       => __( 'Secret Key', 'dokan' ),
        'type'        => 'text',
        'description' => __( 'Get your API keys from your stripe account.', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true
    ],
    'publishable_key' => [
        'title'       => __( 'Publishable Key', 'dokan' ),
        'type'        => 'text',
        'description' => __( 'Get your API keys from your stripe account.', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true
    ],
    'client_id' => [
        'title'       => __( 'Client ID', 'dokan' ),
        'type'        => 'text',
        'description' => __( 'Get your client ID from your stripe account, the Apps menu.', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true
    ],
    'test-credentials-title' => [
        'title' => __( 'Test credentials', 'dokan' ),
        'type'  => 'title',
    ],
    'test_secret_key' => [
        'title'       => __( 'Test Secret Key', 'dokan' ),
        'type'        => 'text',
        'description' => __( 'Get your API keys from your stripe account.', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true
    ],
    'test_publishable_key' => [
        'title'       => __( 'Test Publishable Key', 'dokan' ),
        'type'        => 'text',
        'description' => __( 'Get your API keys from your stripe account.', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true
    ],
    'test_client_id' => [
        'title'       => __( 'Test Client ID', 'dokan' ),
        'type'        => 'text',
        'description' => __( 'Get your client ID from your stripe account, the Apps menu.', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true
    ],
] );
