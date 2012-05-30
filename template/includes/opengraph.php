<?
global $sweeps_campaign;
$title = $sweeps_campaign->getValue('facebook_share_title');
if( !$title ) $title = get_the_title();
?>
<meta property="og:title" content="<?= esc_attr( $title ) ?>" />
<?

$description = $sweeps_campaign->getValue('facebook_share_body');
if( $description ):
?>
<meta property="og:description" content="<?= esc_attr( $description ) ?>" />
<?
endif;

$caption = $sweeps_campaign->getValue('facebook_share_caption');
if( $description ):
?>
<meta property="og:caption" content="<?= esc_attr( $caption) ?>" />
<?
endif;

$image = $sweeps_campaign->getValue('facebook_share_image');
if( $image ):
    $src = wp_get_attachment_image_src( $image, 'og');
    ?>
<meta property="og:image" content="<?= $src[0] ?>" />
    <?
endif;
?>
<meta property="og:type" content="website" />
<meta property="og:url" content="<?= (@$_SERVER["HTTPS"] == "on" ? 'https' : 'http') .'://'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] ?>" />