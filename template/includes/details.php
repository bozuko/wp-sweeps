<?
global $sweeps_campaign;
$prizes = $sweeps_campaign->get_prizes();

$details = $sweeps_campaign->get_form()->field('details_page')->getValue();

if( $details ){
    // print_r($sweeps_campaign);
    echo apply_filters('the_content', $details );
    return;
}

$prizes->rewind_posts();
if( $prizes->post_count ):?>
    <? if( $prizes->post_count > 1 ): ?>
    <h3>Available Prizes</h3>
    <? endif; ?>
    <div class="prizes">
    <? while( $prizes->have_posts() ): $prize = $prizes->next_post(); ?>
        <div class="prize clearfix">
            <? if( $prize->image ): ?>
                <?
                $src = wp_get_attachment_image_src( $prize->image, 'prize');
                ?>
                <img src="<?= $src[0] ?>" class="prize-image" />
            <? endif; ?>
            <div class="prize-details">
                <h5><?
                switch($prizes->current_post):
                    case 0:
                        echo "Grand Prize";
                        break;
                    case 1:
                        echo "First Prize";
                        break;
                    case 2:
                        echo "Second Prize";
                        break;
                    case 3:
                        echo "Third Prize";
                        break;
                endswitch;
                ?></h5>
                <div class="prize-overview">
                    <h4><?= $prize->name ?></h4>
                    <div class="prize-description">
                        <?= apply_filters('the_content', $prize->description) ?>
                    </div>
                </div>
                
                <? if( $prizes->post_count == 1 ): ?>
                
                <div class="detail-row">
                    <h5>Sweepstakes Starts</h5>
                    <?= $sweeps_campaign->getTime('start')->format('F d, Y @ h:i A') ?>
                </div>
                <div class="detail-row">
                    <h5>Sweepstakes Ends</h5>
                    <?= $sweeps_campaign->getTime('end')->format('F d, Y @ h:i A') ?>
                </div>
                <? endif; ?>
            </div>
        </div>
    <? endwhile; ?>
    </div>
<?
endif;
?>