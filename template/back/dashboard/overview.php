<?php
global $campaigns;
$sweeps_campaign = Snap::singleton('Sweeps_Campaign');
?>

<? if( !$campaigns->found_posts ): ?>
<p>You have not created any sweepstakes.</p>
<? return; ?>
<? endif; ?>


<table style="width: 100%;" class="sweeps-table">
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
            <td><?= $sweeps_campaign->isActive() ? 'Active' : $sweeps_campaign->isBeforeStart() ? 'Upcoming' : 'Past' ?></td>
            <td><?= $sweeps_campaign->get_entry_count(get_the_ID()) ?></td>
        </tr>
    <? endwhile; ?>
    </tbody>
</table>

<p><a href="admin.php?page=sweeps">View All</a></p>