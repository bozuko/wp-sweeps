<?php

class Sweeps_Entry_Form extends Snap_Wordpress_Form
{
    /**
     * @form.field.type                 text
     * @form.field.label                Email
     * @form.field.validator.email
     * @form.field.validator.notEmpty
     */
    public $email;
    
    /**
     * @form.field.type                 text
     * @form.field.label                Phone Number
     * @form.field.validator.phone
     * @form.field.validator.notEmpty
     */
    public $phone;
    
    /**
     * @form.field.type                 text
     * @form.field.label                First Name
     * @form.field.validator.notEmpty   true
     */
    public $first_name;
    
    /**
     * @form.field.type                 text
     * @form.field.label                Last Name
     * @form.field.validator.notEmpty
     */
    public $last_name;
    
    /**
     * @form.field.type                 text
     * @form.field.label                Address
     * @form.field.validator.notEmpty
     */
    public $address;
    
    /**
     * @form.field.type                 text
     * @form.field.label                Apt / Suite
     */
    public $address1;
    
    /**
     * @form.field.type                 text
     * @form.field.label                City
     * @form.field.validator.notEmpty
     */
    public $city;
    
    /**
     * @form.field.type                 select
     * @form.field.label                State
     * @form.field.validator.notEmpty
     */
    public $state;
    
    /**
     * @form.field.type                 text
     * @form.field.label                Postal Code
     * @form.field.validator.notEmpty
     */
    public $postal_code;
    
    /**
     * @form.field.type                 day
     * @form.field.label                Date of Birth
     * @form.field.validator.notEmpty
     */
    public $birthday;
    
    /**
     * @form.field.type                 checkbox
     * @form.field.label                I agree to the <a href="#official-rules">Official Rules</a>
     * @form.field.validator.notEmpty   You must agree to the Official Rules
     */
    public $agree;
    
    public function getOptions( $name )
    {
        switch( $name ){
            case 'state':
                return array(
                    ''  =>'Select State',
                    'AL'=>"Alabama",
                    'AK'=>"Alaska",
                    'AZ'=>"Arizona",
                    'AR'=>"Arkansas",
                    'CA'=>"California",
                    'CO'=>"Colorado",
                    'CT'=>"Connecticut",
                    'DE'=>"Delaware",
                    'DC'=>"District Of Columbia",
                    'FL'=>"Florida",
                    'GA'=>"Georgia",
                    'HI'=>"Hawaii",
                    'ID'=>"Idaho",
                    'IL'=>"Illinois",
                    'IN'=>"Indiana",
                    'IA'=>"Iowa",
                    'KS'=>"Kansas",
                    'KY'=>"Kentucky",
                    'LA'=>"Louisiana",
                    'ME'=>"Maine",
                    'MD'=>"Maryland",
                    'MA'=>"Massachusetts",
                    'MI'=>"Michigan",
                    'MN'=>"Minnesota",
                    'MS'=>"Mississippi",
                    'MO'=>"Missouri",
                    'MT'=>"Montana",
                    'NE'=>"Nebraska",
                    'NV'=>"Nevada",
                    'NH'=>"New Hampshire",
                    'NJ'=>"New Jersey",
                    'NM'=>"New Mexico",
                    'NY'=>"New York",
                    'NC'=>"North Carolina",
                    'ND'=>"North Dakota",
                    'OH'=>"Ohio",
                    'OK'=>"Oklahoma",
                    'OR'=>"Oregon",
                    'PA'=>"Pennsylvania",
                    'RI'=>"Rhode Island",
                    'SC'=>"South Carolina",
                    'SD'=>"South Dakota",
                    'TN'=>"Tennessee",
                    'TX'=>"Texas",
                    'UT'=>"Utah",
                    'VT'=>"Vermont",
                    'VA'=>"Virginia",
                    'WA'=>"Washington",
                    'WV'=>"West Virginia",
                    'WI'=>"Wisconsin",
                    'WY'=>"Wyoming");
                
            default:
                return parent::getOptions( $name );

        }
    }
    
}