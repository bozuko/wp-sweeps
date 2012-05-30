<?php
global $sweeps_campaign;
/*
$sweeps_campaign->getEntryForm()->render(array(
    'renderer'  => 'Snap_Wordpress_Form_Renderer_Foundation',
    'action'    => add_query_arg('submit_form','1'),
    'buttons'   => array(
        array(
            'text' => 'Enter Sweepstakes'
        )
    )
));
*/
?>
<div class="the-form">
<div class="indented-form">
<?
$form = $sweeps_campaign->getEntryForm();
$renderer =& Snap::inst('Snap_Wordpress_Form_Renderer_Foundation');
$sweeps_campaign->open_form( $renderer );
?>

<div class="messages">
    
</div>

<div class="row">
    <div class="six columns">
        <?php
        if( $form->field('first_name') ) $renderer->renderField( $form->field('first_name') );
        if( $form->field('last_name') ) $renderer->renderField( $form->field('last_name') );
        if( $form->field('email') ) $renderer->renderField( $form->field('email') );
        if( $form->field('phone') ) $renderer->renderField( $form->field('phone') );
        if( $form->field('birthday') ) $renderer->renderField( $form->field('birthday') );
        ?>

    </div>
    <div class="six columns">
        <?
        if( $form->field('address') ) $renderer->renderField( $form->field('address') );
        if( $form->field('address1') ) $renderer->renderField( $form->field('address1') );
        if( $form->field('city') ) $renderer->renderField( $form->field('city') );
        if( $form->field('state') ) $renderer->renderField( $form->field('state') );
        if( $form->field('postal_code') ) $renderer->renderField( $form->field('postal_code') );
        ?>
    </div>
</div>
<div class="agree-box">
    <? $renderer->renderField( $form->field('agree') ) ?>
</div>
<?

$renderer->renderButtons(array(
    array(
        'text'      => 'Enter',
        'type'      => 'submit'
    )
));
?>
<div style="text-align:right;margin-bottom: 10px; ">
    * Required Field
</div>
<?
$renderer->renderCloseForm();
?>
</div>
</div>