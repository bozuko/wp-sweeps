<?
global $sweeps_campaign;
$before_start = $sweeps_campaign->getValue('before_start_message');
?>
<div class="grey-box" style="text-align: center;">
    <? if( $before_start ): ?>
    <?= apply_filters('the_content', $before_start ) ?>
    <? else: ?>
    <h3>This promotion has not started yet.</h3>
    <p>The promotion starts <?= $sweeps_campaign->getStart()->format('F d, Y \a\t h:i A') ?></p>
    <? endif; ?>
</div>