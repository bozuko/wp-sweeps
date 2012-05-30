<?php

class Sweeps_Validator_UniquePhone extends Snap_Wordpress_Form_Validator
{
    protected $message = "This phone number has already been entered into this sweepstakes.";
    
    function isValid()
    {
        
        if( $this->field && ($form = $this->field->getForm()) && ($idField = $form->field('ID')) && ($id = $idField->getValue()) ){
            return true;
        }
        
        // see if we already have an entry for this contest
        $query = new WP_Query(array(
            'post_type'     => 'sweep_entry',
            'meta_key'      => 'phone',
            'meta_value'    => strtolower( trim( $this->value ) )
        ));
        $exists = $query->have_posts();
        wp_reset_postdata();
        return empty( $this->value ) || !$exists;
    }
}