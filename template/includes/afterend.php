<?
global $sweeps_campaign;
$message = $sweeps_campaign->getValue('after_end_message');
?>
<div class="grey-box" style="text-align:center;">
    <? if( $message ): ?>
    <?= apply_filters('the_content', $message ) ?>
    <? else: ?>
    <h3>This promotion has ended.</h3>
    <? endif; ?>
</div>