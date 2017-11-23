<?php
$changelog = array(
    array(
        'version'  => 'Version 2.7.0',
        'released' => '2017-11-23',
        'changes' => array(
            array(
                'title'       => 'Introducing All New modules and packaging System',
                'type'        => 'New',
                'description' => 'Say bye bye to previous add-ons, which were very difficult to manage. From our new update, we are going to transform all our add-ons into modules. Guess what, you will be able to manage all of them from a single place. So, we have added a new menu called ‘Modules’ and removed the old ‘Add-ons’ menu. This is how the new page looks like.
                <img src="https://wedevs.com/wp-content/uploads/2017/11/Dokan-new-module-activation-deactivation.gif" alt="Dokan Module">'
            ),
            array(
                'title'       => 'Automatic Updates for Modules',
                'type'        => 'New',
                'description' => 'Previously, you didn’t get a live update for any of the Dokan add-ons. Now, you can manage them from a single place as well as get live updates directly with Dokan plugin. So, no more manual updates! You don’t have to download each add-ons and install them separately every time you get an update.'
            ),
            array(
                'title'       => 'Interactive Settings Page to Manage it All',
                'type'        => 'New',
                'description' => 'Dokan now has better and improved settings page where you can easily configure everything for your Dokan Multivendor.
                <img src="https://wedevs-com-wedevs.netdna-ssl.com/wp-content/uploads/2017/11/dokan-new-settings-page.png" alt="Dokan Settings">'
            ),
            array(
                'title'       => 'Shipping options showing for product edit while dokan shipping is disabled',
                'type'        => 'Fix',
                'description' => 'Shipping option all time showing in edit product page due to disable dokan shipping option. This problem fixed if dokan main shipping is disabled then shipping option are not showing in edit product page
                <img src="https://wedevs-com-wedevs.netdna-ssl.com/wp-content/uploads/2017/11/dokan-new-settings-page.png" alt="Dokan Settings">'
            ),

        )
    )
);

function _dokan_changelog_content( $content ) {
    $content = wpautop( $content, true );

    return $content;
}
?>

<div class="wrap dokan-whats-new">
    <h1>What's New in Dokan?</h1>

    <div class="wedevs-changelog-wrapper">

        <?php foreach ( $changelog as $release ) { ?>
            <div class="wedevs-changelog">
                <div class="wedevs-changelog-version">
                    <h3><?php echo esc_html( $release['version'] ); ?></h3>
                    <p class="released">
                        (<?php echo human_time_diff( time(), strtotime( $release['released'] ) ); ?> ago)
                    </p>
                </div>
                <div class="wedevs-changelog-history">
                    <ul>
                        <?php foreach ( $release['changes'] as $change ) { ?>
                            <li>
                                <h4>
                                    <span class="title"><?php echo esc_html( $change['title'] ); ?></span>
                                    <span class="label <?php echo strtolower( $change['type'] ); ?>"><?php echo esc_html( $change['type'] ); ?></span>
                                </h4>

                                <div class="description">
                                    <?php echo _dokan_changelog_content( $change['description'] ); ?>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        <?php } ?>
    </div>

</div>
<?php 
    $offer_key = 'dokan_whats_new_notice';
    update_option( $offer_key . '_tracking_notice', 'hide' );
?>
<style type="text/css">

.error, .udpated {
    display: none;
}

.dokan-whats-new h1 {
    text-align: center;
    margin-top: 20px;
    font-size: 30px;
}

.wedevs-changelog {
    display: flex;
    max-width: 920px;
    border: 1px solid #e5e5e5;
    padding: 12px 20px 20px 20px;
    margin: 20px auto;
    background: #fff;
    box-shadow: 0 1px 1px rgba(0,0,0,0.04);
}

.wedevs-changelog-wrapper .wedevs-support-help {

}

.wedevs-changelog .wedevs-changelog-version {
    width: 360px;
}

.wedevs-changelog .wedevs-changelog-version .released {
    font-style: italic;
}

.wedevs-changelog .wedevs-changelog-history {
    width: 100%;
    font-size: 14px;
}

.wedevs-changelog .wedevs-changelog-history li {
    margin-bottom: 30px;
}

.wedevs-changelog .wedevs-changelog-history h4 {
    margin: 0 0 10px 0;
    font-size: 1.3em;
    line-height: 26px;
}

.wedevs-changelog .wedevs-changelog-history p {
    font-size: 14px;
    line-height: 1.5;
}

.wedevs-changelog .wedevs-changelog-history img {
    margin-top: 30px;
    max-width: 100%;
}

.wedevs-changelog-history span.label {
    margin-left: 10px;
    position: relative;
    color: #fff;
    border-radius: 20px;
    padding: 0 8px;
    font-size: 12px;
    height: 20px;
    line-height: 19px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    font-weight: normal;
}

span.label.new {
    background: #3778ff;
    border: 1px solid #3778ff;
}

span.label.improvement {
    background: #3aaa55;
    border: 1px solid #3aaa
}

span.label.fix {
    background: #ff4772;
    border: 1px solid #ff4772;
}

</style>
