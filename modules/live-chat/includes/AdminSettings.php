<?php

namespace WeDevs\DokanPro\Modules\LiveChat;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan_Live_Chat_Settings Class
 */
class AdminSettings {

    /**
     * Constructor method for this class
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize all the hooks
     *
     * @since 1.0
     *
     * @return void
     */
    public function init_hooks() {
        add_filter( 'dokan_settings_sections', array( $this, 'dokan_live_chat_sections' ), 20, 1 );
        add_filter( 'dokan_settings_fields', array( $this, 'dokan_live_chat_settings' ), 20, 1 );
    }

    /**
     * Add dokan live caht secitons in doakn admin settings
     *
     * @param  array $sections
     *
     * @since 1.0
     *
     * @return array $sections;
     */
    public function dokan_live_chat_sections( $sections ) {
        $sections[] = array(
            'id'    => 'dokan_live_chat',
            'title' => __( 'Live Chat', 'dokan' ),
            'icon'  => 'dashicons-format-chat'
        );

        return $sections;
    }

    /**
     * Register dokan live chat settings
     *
     * @param  array $settings
     *
     * @since 1.0
     *
     * @return array $settings
     */
    public function dokan_live_chat_settings( $settings ) {
        $settings['dokan_live_chat'] = [
            'enable' => [
                'name'=> 'enable',
                'label' => __( 'Enable Live Chat', 'dokan' ),
                'desc'  => __( 'Enable live chat between vendor and customer', 'dokan' ),
                'type'  => 'checkbox',
                'default' => 'on',
            ],
            'provider' => [
                'name'               => 'provider',
                'label'              => __( 'Chat Provider', 'dokan' ),
                'desc'               => __( 'Which chat provider you want to setup in your site?', '' ),
                'refresh_after_save' => true,
                'type'               => 'radio',
                'default'            => 'talkjs',
                'options'            => [
                    'messenger' => __( 'Facebook Messenger', 'dokan' ),
                    'talkjs'    => __( 'TalkJS', 'dokan' ),
                ],
            ],
            'theme_color' => [
                'name'    => 'theme_color',
                'label'   => __( 'Messenger Color', 'dokan' ),
                'type'    => 'color',
                'default' => '#0084FF',
                'show_if' => [
                    'provider' => [
                        'equal' => 'messenger'
                    ]
                ]
            ],
            'app_id' => [
                'name'    => 'app_id',
                'label' => __( 'App ID', 'dokan' ),
                'desc'  => sprintf( '%s <a target="_blank" href="%s">%s</a>', __( 'Insert App ID', 'dokan' ), esc_url( 'https://talkjs.com/dashboard/signup/standard/'), __( '( Get your App ID )', 'dokan' ) ),
                'type'  => 'text',
                'show_if' => [
                    'provider' => [
                        'equal' => 'talkjs',
                    ]
                ],
            ],
            'app_secret' => [
                'name'  => 'app_secret',
                'label' => __( 'App Secret', 'dokan' ),
                'desc'  => sprintf( '%s <a target="_blank" href="%s">%s</a>', __( 'Insert App Secret', 'dokan' ), esc_url( 'https://talkjs.com/dashboard/signup/standard/'), __( '( Get your App Secret )', 'dokan' ) ),
                'type'  => 'text',
                'show_if' => [
                    'provider' => [
                        'equal' => 'talkjs',
                    ]
                ],
            ],
            'chat_button_seller_page' => [
                'name'  => 'chat_button_seller_page',
                'label' => __( 'Chat Button on Vendor Page', 'dokan' ),
                'desc'  => __( 'Show chat button on vendor page', 'dokan' ),
                'type'  => 'checkbox',
            ],
            'chat_button_product_page' => [
                'name'  => 'chat_button_product_page',
                'label' => __( 'Chat Button on Product Page', 'dokan' ),
                'desc'  => __( 'Show chat button on product page', 'dokan' ),
                'type'  => 'select',
                'options' => [
                    'above_tab' => __( 'Above Product Tab', 'dokan' ),
                    'inside_tab' => __( 'Inside Product Tab', 'dokan' ),
                    'dont_show' => __( 'Don\'t Show', 'dokan' ),
                ]
            ],
        ];

        return $settings;
    }

    /**
     * Check if live chat is enabled
     *
     * @since 3.0.0
     *
     * @return boolean
     */
    public static function is_enabled() {
        if ( 'messenger' === self::get_provider() ) {
            return dokan_validate_boolean( dokan_get_option( 'enable', 'dokan_live_chat' ) );
        }

        if ( self::get_app_id() && self::get_app_secret() ) {
            return dokan_validate_boolean( dokan_get_option( 'enable', 'dokan_live_chat' ) );
        }
    }

    /**
     * Get provider
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public static function get_provider() {
        return dokan_get_option( 'provider', 'dokan_live_chat' );
    }

    /**
     * Get theme color for fb messenger
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public static function get_theme_color() {
        return dokan_get_option( 'theme_color', 'dokan_live_chat' );
    }

    /**
     * Get the App ID
     *
     * @since 3.0.0
     *
     * @return string
     */
    public static function get_app_id() {
        return dokan_get_option( 'app_id', 'dokan_live_chat' );
    }

    /**
     * Get the App Secret
     *
     * @since 3.0.0
     *
     * @return string
     */
    public static function get_app_secret() {
        return dokan_get_option( 'app_secret', 'dokan_live_chat' );
    }

    /**
     * Check whether chat button should be displaied on store page or not
     *
     * @since 3.0.0
     *
     * @return boolean
     */
    public static function show_chat_on_store_page() {
        return dokan_validate_boolean( dokan_get_option( 'chat_button_seller_page', 'dokan_live_chat' ) );
    }

    /**
     * Check whether chat button should be displaied on inside product tab or not
     *
     * @since 3.0.0
     *
     * @return boolean
     */
    public static function show_chat_on_product_tab() {
        return 'inside_tab' === dokan_get_option( 'chat_button_product_page', 'dokan_live_chat' );
    }

    /**
     * Check whether chat button should be displaied on above product tab or not
     *
     * @since 3.0.0
     *
     * @return boolean
     */
    public static function show_chat_above_product_tab() {
        return 'above_tab' === dokan_get_option( 'chat_button_product_page', 'dokan_live_chat' );
    }
}