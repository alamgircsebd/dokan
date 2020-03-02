<?php

namespace WeDevs\DokanPro\Abstracts;

use WeDevs\Dokan\Abstracts\DokanUpgrader;

class DokanProUpgrader extends DokanUpgrader {

    /**
     * Get db versioning key
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return string
     */
    public static function get_db_version_key() {
        return dokan_pro()->get_db_version_key();
    }
}
