<?
global $campaign;
?>
<div class="header">
    <img class="image-810" src="<? $_src = wp_get_attachment_image_src( $campaign->image810, 'sweeps810'); echo $_src[0]; ?>" alt="<? esc_attr( $campaign->post_title ) ?>" />
    <img class="image-520" src="<? $_src = wp_get_attachment_image_src( $campaign->image520, 'sweeps520'); echo $_src[0]; ?>" alt="<? esc_attr( $campaign->post_title ) ?>" />
    <img class="image-400" src="<? $_src = wp_get_attachment_image_src( $campaign->image400, 'sweeps400'); echo $_src[0]; ?>" alt="<? esc_attr( $campaign->post_title ) ?>" />
</div>