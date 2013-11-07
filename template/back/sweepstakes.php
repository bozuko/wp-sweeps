<?php
global $campaigns, $counts;
$sweeps_campaign = Snap::singleton('Sweeps_Campaign');
?>

<div class="wrap">

    <h2>Sweepstakes Manager</h2>
    
    <? if( !$campaigns->found_posts ): ?>
    <p>You have not created any sweepstakes.</p>
    <? return; ?>
    <? endif; ?>
    
    <table style="width: 100%;" class="sweeps-table" cellspacing="0" >
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Entries</th>
            </tr>
        </thead>
        <tbody> 
        <? while( $campaigns->have_posts() ): ?>
            <? $campaigns->the_post() ?>
            <tr>
                <td><?= get_the_title() ?></td>
                <td><?= $sweeps_campaign->isActive() ? 'Active' : ($sweeps_campaign->isBeforeStart() ? 'Upcoming' : 'Past') ?></td>
                <td>
                    <?= $sweeps_campaign->get_entries()->found_posts ?>
                    <a href="?sweep_download_entries=<?= get_the_ID(); ?>">Download</a>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="entry-chart" data-counts='<?= json_encode($counts) ?>'></div>
                </td>
            </tr>
        <? endwhile; ?>
        </tbody>
    </table>
    
</div>
<?php
wp_reset_postdata();
wp_reset_query();
?>