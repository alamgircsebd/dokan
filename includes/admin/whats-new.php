<?php
$changelog = array(
    array(
        'version'  => 'Version 2.7.0',
        'released' => '2017-11-23',
        'changes' => array(
            array(
                'title'       => 'Externally triggering widget popover',
                'type'        => 'Improvement',
                'description' => 'Until today you could only trigger the widget popover only by clicking on the badge, but sometimes it makes sense to toggle the popover from an external link, say like we do above, with the Release notes link.'
            ),
            array(
                'title'       => 'Scheduled publishing',
                'type'        => 'New',
                'description' => 'Pretty self-explanatory – just pick a date in the future, publish the post and we\'ll handle the rest!.'
            ),
            array(
                'title'       => 'Saving is back... Don\'t ask.',
                'type'        => 'Fix',
                'description' => '<p>A bit of a regression happened today, we updated some under-the-hood things and unfortunately broke saving posts along the way.</p>
<p>Issue is now solved. Sorry for the trouble.</p>'
            ),

        )
    ),
    array(
        'version'  => 'Version 2.6.8',
        'released' => '2017-10-13',
        'changes' => array(
            array(
                'title'       => 'Externally triggering widget popover',
                'type'        => 'Improvement',
                'description' => 'Until today you could only trigger the widget popover only by clicking on the badge, but sometimes it makes sense to toggle the popover from an external link, say like we do above, with the Release notes link.'
            ),
            array(
                'title'       => 'Scheduled publishing',
                'type'        => 'New',
                'description' => 'Pretty self-explanatory – just pick a date in the future, publish the post and we\'ll handle the rest!.'
            ),

        )
    ),
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

<style type="text/css">

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
}

.wedevs-changelog .wedevs-changelog-history p {
    font-size: 14px;
    line-height: 1.5;
}

.wedevs-changelog .wedevs-changelog-history img {
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
