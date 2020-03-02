<?php

namespace WeDevs\DokanPro\Upgrade;

class Upgrades {

    /**
     * List of upgrades
     *
     * Add array element like
     * `2.5.0 => [ 'upgrader' => Upgrades\V_2_5_0::class, 'require' => '2.8.0' ]`
     * where `require` is the the last version found in \WeDevs\Dokan\Upgrade\Upgrades
     * class.
     *
     * @since DOKAN_PRO_SINCE
     *
     * @var array
     */
    private static $upgrades = [];

    /**
     * Get DB installed version number
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public static function get_db_installed_version() {
        return get_option( dokan_pro()->get_db_version_key(), null );
    }

    /**
     * Detects if upgrade is required
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param bool $is_required
     *
     * @return bool
     */
    public static function is_upgrade_required( $is_required = false ) {
        $installed_version = self::get_db_installed_version();
        $upgrade_versions  = array_keys( self::$upgrades );

        if ( $installed_version && version_compare( $installed_version, end( $upgrade_versions ), '<' ) ) {
            return true;
        }

        return $is_required;
    }

    /**
     * Update Dokan Pro version number in DB
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public static function update_db_dokan_pro_version() {
        $installed_version = self::get_db_installed_version();

        if ( version_compare( $installed_version, DOKAN_PRO_PLUGIN_VERSION, '<' ) ) {
            update_option( dokan_pro()->get_db_version_key(), DOKAN_PRO_PLUGIN_VERSION );
        }
    }

    /**
     * Get upgrades
     *
     * @since DOKAN_PRO_SINCE
     *
     * @param array $upgrades
     *
     * @return array
     */
    public static function get_upgrades( $upgrades = [] ) {
        if ( ! self::is_upgrade_required() ) {
            return $upgrades;
        }

        $installed_version = self::get_db_installed_version();

        foreach ( self::$upgrades as $version => $upgrade ) {
            if ( version_compare( $installed_version , $version, '<' ) ) {
                $upgrades[ $upgrade['require'] ][] = $upgrade;
            }
        }

        return $upgrades;
    }
}
