<?php
global $campaign, $sweeps_campaign;
$bodyClasses = array('sweep-campaign');
if( $sweeps_campaign->isFacebookTab() ){
	//$bodyClasses[] = 'facebook-tab';
}
echo "<!doctype html>\n";
?>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title><? wp_title('') ?></title>
	<? wp_head() ?>
	
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="<?= SWEEPS_URL ?>/resources/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?= SWEEPS_URL ?>/template/css/style.css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script src="<?= SWEEPS_URL ?>/resources/js/modernizr-2.5.3-respond-1.1.0.min.js"></script>
	<script src="<?= SWEEPS_URL ?>/resources/bootstrap/js/bootstrap.min.js"></script>
	<script src="<?= SWEEPS_URL ?>/template/js/script.js"></script>
</head>
<body class="<?= implode(' ', $bodyClasses) ?>">
    <div class="wrapper">
        <header>
            <div class="banner">
                <img class="image-760" src="<?= $campaign['image760'] ?>" alt="<? esc_attr( $campaign['title'] ) ?>" />
                <img class="image-520" src="<?= $campaign['image520'] ?>" alt="<? esc_attr( $campaign['title'] ) ?>" />
                <img class="image-400" src="<?= $campaign['image400'] ?>" alt="<? esc_attr( $campaign['title'] ) ?>" />
            </div>
            <h1><? the_title() ?></h1>
            <nav>
                <ul class="nav nav-tabs">
                    <li class="form active"><a data-toggle="tab" href="#form">Enter Sweepstakes</a></li>
                    <li class="rules"><a data-toggle="tab" href="#rules">Official Rules</a></li>
                    
                </ul>
            </nav>
        </header>
        
        <div class="tab-content main">
            
            <section class="tab-pane active" id="form">
                <div class="content">
					<? if( $sweeps_campaign->isAgeRestricted() ){ ?>
					<div class="alert alert-error alert-block">
						<h4 class="alert-heading">
							Sorry, you are not eligible to enter.  See official rules.
						</h4>
					</div>
					<? } else { ?>
					<div class="intro">
						<?= $sweeps_campaign->intro() ?>
					</div>
					<div class="the-form">
						<?= $sweeps_campaign->entryForm() ?>
					</div>
					<? } ?>
                </div>
            </section>
            
            <section class="tab-pane" id="rules">
                <div class="content">
                    <?= $sweeps_campaign->rules() ?>
                </div>
            </section>
			
			<? if( !$sweeps_campaign->isFacebookLiked() ){ ?>
			<div class="like-us">
				<img class="like-us-arrow" src="<?= SWEEPS_URL ?>/template/images/like-us-arrow.png" />
				<h3>Like us above to enter this<br />demonstration sweepstakes!</h3>
			</div>
			<? } ?>
            
        </div>
        
        <footer>
            
        </footer>
	</div>
	<div id="fb-root"></div>
	<script src="//connect.facebook.net/en_US/all.js"></script>
    <script>
        var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
        (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
        g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
        s.parentNode.insertBefore(g,s)}(document,'script'));
    </script>
    <?php wp_footer(); ?>
</body>
</html>