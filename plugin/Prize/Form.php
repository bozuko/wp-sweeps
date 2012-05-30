<?php

class Sweeps_Prize_Form extends Snap_Wordpress_Form
{
    /**
     * @form.field.type             text
     * @form.field.label            Prize Name
     */
    public $name;
    /**
     * @form.field.type             text
     * @form.field.label            Quantity
     */
    public $quantity;
    /**
     * @form.field.type             textarea
     * @form.field.label            Prize Description
     * @form.field.hide_label       false
     */
    public $description;
    
    /**
     * @form.field.type             wysiwyg
     * @form.field.label            Prize Email
     * @form.field.hide_label       false
     */
    //public $email;
    
    /**
     * @form.field.type             text
     * @form.field.label            Approximate Retail Value
     */
    public $arv;
    
    /**
     * @form.field.type             image
     * @form.field.image_size       prize
     * @form.field.use_id
     * @form.field.label            Image
     * @form.field.display_image
     */
    public $image;
}