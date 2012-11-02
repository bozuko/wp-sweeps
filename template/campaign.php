<?php

global $campaign, $sweeps_campaign;

echo '<!DOCTYPE html>';
function _template_url($path=''){
    return SWEEPS_TEMPLATE_URL.$path;
}
// add our urls
$z_styles = array('globals','typography','grid','ui','forms','mobile');
foreach( $z_styles as $z_style ){
    wp_enqueue_style( 'zurb-'.$z_style, _template_url("/zurb-foundation/stylesheets/$z_style.css") );
}
wp_enqueue_style( 'campaign', _template_url('/css/campaign.css') );

wp_enqueue_script( 'prefix-free', _template_url('/js/prefix-free.js'), array('jquery') );
wp_enqueue_script( 'modernizr', _template_url('/js/modernizr.js'), array('jquery') );
wp_enqueue_script( 'customforms', _template_url('/zurb-foundation/javascripts/jquery.customforms.js') );

?>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />
	
	<? Snap_Wordpress_Template::load('sweeps', 'includes/opengraph') ?>

    <title><?= the_title() ?></title>
	<!-- Set the viewport width to device width for mobile -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />

	<? wp_head() ?>
  
    <!--[if lt IE 9]>
	<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <link rel="stylesheet" href="<?= _template_url('/zurb-foundation/stylesheets/ie.css') ?>">
	<![endif]-->

</head>

<body <?= body_class() ?>>
	<div id="fb-root"></div>
	<? do_action('sweeps_after_body') ?>

    <div class="container">
		<? Snap_Wordpress_Template::load('sweeps', 'includes/header') ?>
		<? Snap_Wordpress_Template::load('sweeps', 'includes/nav') ?>
		<div class="main">
			<ul class="tabs-content">
				<li id="enter-tab" class="tab-content active">
					
					<?
					/* Disable form conditions */
					$masks = array();
					$masks = apply_filters('sweeps_entry_mask', $masks);
					$mask_count = 0;
					if( count($masks) ): foreach( $masks as $i => $mask ):
						$classes = isset($mask['classes']) && is_array($mask['classes']) ? $mask['classes'] : array();
						if( $mask_count++ == 0 ) $classes[] = 'active-mask';
						$id = @$mask['id'];
						if( !$id ) $id = 'entry-mask-'.$i;
						?>
						<div id="<?= $id ?>" class="entry-mask <?=implode(' ',$classes) ?>">
							<div class="bg"></div>
							<?= @$mask['before_content'] ?>
							<div class="content">
								<div class="content-body"><?= @$mask['content'] ?></div>
							</div>
							<?= @$mask['after_content'] ?>
						</div>
					<? endforeach; endif; ?>
					<?
					/* End disable form conditions */
					?>
					
					<? if( !$sweeps_campaign->isActive() ): ?>
						<? if( $sweeps_campaign->isBeforeStart() ): ?>
							<? Snap_Wordpress_Template::load('sweeps', 'includes/beforestart') ?>
						<? else: ?>
							<? Snap_Wordpress_Template::load('sweeps', 'includes/afterend') ?>
						<? endif; ?>
					<? else : ?>
						<? do_action('sweep_before_form') ?>
						<? if( apply_filters('sweep_display_form', true) ): ?>
							<? if( $sweeps_campaign->isSuccess() ): ?>
								<? Snap_Wordpress_Template::load('sweeps', 'includes/thankyou') ?>
							<? else: ?>
								<? Snap_Wordpress_Template::load('sweeps', 'includes/introduction') ?>
								<? Snap_Wordpress_Template::load('sweeps', 'includes/form') ?>
							<? endif; ?>
						<? endif; ?>
						<? do_action('sweep_after_form') ?>
					<? endif; ?>
				</li>
				<li id="details-tab" class="tab-content" >
					<? Snap_Wordpress_Template::load('sweeps', 'includes/details') ?>
				</li>
			</ul>
			<? if( $sweeps_campaign->isFacebookConnect() ){ ?>
			<div class="facebook-user-block"></div>
			<? } ?>
			<div class="official-rules" id="official-rules">
				<h3>Official Rules</h3>
				<?= $sweeps_campaign->rules() ?>
			</div>
		</div>
    </div>
	<div class="footer">
		
	</div>
	<? if( ($code = $sweeps_campaign->get_google_analytics_code()) ): ?> 
    <script>
        var _gaq=[['_setAccount', '<?= $code ?>'],['_trackPageview']];
        (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
        g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
        s.parentNode.insertBefore(g,s)}(document,'script'));
    </script>
	<? endif; ?>
    <? wp_footer(); ?>
</body>
</html>