<?php

class Sweeps_Validator_UniqueEmail extends Snap_Wordpress_Form_Validator
{
    protected $message = "This email has already been entered into this sweepstakes.";
    
    function isValid()
    {
        
        if( $this->field && ($form = $this->field->getForm()) && ($idField = $form->field('ID')) && ($id = $idField->getValue()) ){
            return true;
        }
        
        $filtered = $this->filter();
        
        $args = array(
            'post_type'     => 'sweep_entry',
            'meta_key'      => 'email',
            'meta_value'    => strtolower( $filtered )
        );
        
        // see if we already have an entry for this contest
        $query = new WP_Query($args);
        $exists = $query->have_posts();
        wp_reset_postdata();
        
        return empty( $filtered ) || !$exists;
    }
}