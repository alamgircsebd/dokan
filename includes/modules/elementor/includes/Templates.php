<?php

namespace DokanPro\Modules\Elementor;

use Dokan\Traits\Singleton;

class Templates {

    use Singleton;

    public function boot() {
        add_filter( 'elementor/api/get_templates/body_args', [ self::class, 'add_http_request_filter' ] );

        add_filter( 'option_' . \Elementor\Api::LIBRARY_OPTION_KEY, [ self::class, 'add_template_library' ] );
    }

    /**
     * Filter elementor https request
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $body_args
     */
    public static function add_http_request_filter( $body_args ) {
        add_filter( 'pre_http_request', [ self::class, 'pre_http_request' ], 10, 3 );

        return $body_args;
    }

    /**
     * Returns dokan templates for related request
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param bool   $pre
     * @param array  $r
     * @param string $url
     *
     * @return bool|array
     */
    public static function pre_http_request( $pre, $r, $url ) {
        // @see elementor/includes/api.php $api_get_template_content_url
        if ( preg_match( '/https\:\/\/my\.elementor\.com\/api\/v1\/templates\/100000(\d+)/', $url, $matches ) ) {
            $json_file = DOKAN_ELEMENTOR_PATH . '/template-library/' . $matches[1] . '.json';

            if ( file_exists( $json_file ) ) {
                $content = json_decode( file_get_contents( $json_file ), true );

                return [
                    'response' => [
                        'code' => 200,
                    ],
                    'body' => json_encode( $content )
                ];
            }
        }

        return $pre;
    }

    /**
     * Add Dokan templates as remote template source
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $value
     */
    public static function add_template_library( $value ) {
        $value['categories'][] = 'single store';

        $store_templates = [
            [
                'id'                => "1000003",
                'source'            => "remote",
                'type'              => "block",
                'subtype'           => "single store",
                'title'             => "Store Header Layout 3",
                'thumbnail'         => DOKAN_ELEMENTOR_ASSETS . '/images/store-header.png',
                'tmpl_created'      => "1475067229",
                'author'            => "weDevs",
                'tags'              => '',
                'is_pro'            => false,
                'popularity_index'  => 1,
                'trend_index'       => 1,
                'favorite'          => false,
                'has_page_settings' => false,
                'url'               => 'https://wedevs.com',
            ]
        ];

        $value['templates'] = array_merge( $value['templates'], $store_templates );

        return $value;
    }
}
