<?php

class Sweeps_Campaign_Form extends Snap_Wordpress_Form
{
    /**
     * @form.field.type             date
     * @form.field.label            Start Date
     * @form.field.group            publishing
     */
    public $start_date;
    
    /**
     * @form.field.type             time
     * @form.field.label            Start Time
     * @form.field.group            publishing
     */
    public $start_time;
    
    /**
     * @form.field.type             date
     * @form.field.label            End Date
     * @form.field.group            publishing
     */
    public $end_date;
    
    /**
     * @form.field.type             time
     * @form.field.label            End Time
     * @form.field.group            publishing
     */
    public $end_time;
    
    /**
     * @form.field.type             select
     * @form.field.label            Timezone
     * @form.field.group            publishing
     */
    public $timezone;
    
    /**
     * @form.field.type             text
     * @form.field.label            Age Restriction
     * @form.field.group            publishing
     */
    public $ageRestriction;
    
    /**
     * @form.field.type             text
     * @form.field.label            Production URL
     * @form.field.group            publishing
     */
    public $productionUrl;
    
    /**
     * @form.field.type             text
     * @form.field.label            Pro Texting Username
     * @form.field.group            publishing
     */
    public $proTextingUsername;
    
    /**
     * @form.field.type             password
     * @form.field.label            Pro Texting Password
     * @form.field.group            publishing
     */
    public $proTextingPassword;
    
    /**
     * @form.field.type             text
     * @form.field.label            Pro Texting Keyword
     * @form.field.group            publishing
     */
    public $proTextingKeyword;
    
    /**
     * @form.field.type             textarea
     * @form.field.label            SMS Auto Reply
     * @form.field.group            publishing
     */
    public $smsReply;
    
    /**
     * @form.field.type             text
     * @form.field.label            Facebook Tab URL
     * @form.field.group            publishing
     */
    public $facebookTabUrl;
    
    /**
     * @form.field.type             text
     * @form.field.label            Google Analytics Code
     * @form.field.group            publishing
     */
    public $googleAnalyticsCode;
    
    /**
     * @form.field.type             select
     * @form.field.label            Notifications
     * @form.field.group            publishing
     */
    public $notifications;
    
    /**
     * @form.field.type             text
     * @form.field.label            Notification Emails
     * @form.field.group            publishing
     */
    public $notificationEmails;
    
    
    /**
     * @form.field.type             select
     * @form.field.label            Like Gate
     * @form.field.group            facebook_app
     * @form.field.description      If this is set to "Always", we will collect Facebook Ids and users must accept basic permissions of the custom app.
     */
    public $likeGate;
    
    /**
     * @form.field.type             select
     * @form.field.label            Facebook Connect
     * @form.field.group            facebook_app
     * @form.field.description      If this is enabled, the user must accept Facebook app permissions before entering.
     */
    public $facebookConnect;
    
    /**
     * @form.field.type             text
     * @form.field.label            Facebook Page Id
     * @form.field.group            facebook_app
     * @form.field.description      If Like Gate is set to always, you must include the page id
     */
    public $facebookPageId;
    
    /**
     * @form.field.type             text
     * @form.field.label            Facebook Page Url
     * @form.field.group            facebook_app
     * @form.field.description      If Like Gate is set to always, you must include the page url
     */
    public $facebookPageUrl;
    
    /**
     * @form.field.type             text
     * @form.field.label            Facebook Application Id
     * @form.field.group            facebook_app
     */
    public $facebook_id;
    
    /**
     * @form.field.type             text
     * @form.field.label            Facebook Application Secret
     * @form.field.group            facebook_app
     */
    public $facebook_secret;
    
    /**
     * @form.field.type             text
     * @form.field.label            Facebook Share Title
     * @form.field.group            facebook_share
     */
    public $facebook_share_title;
    
    /**
     * @form.field.type             text
     * @form.field.label            Facebook Share Caption
     * @form.field.group            facebook_share
     */
    public $facebook_share_caption;
    
    /**
     * @form.field.type             textarea
     * @form.field.label            Facebook Share Body
     * @form.field.group            facebook_share
     */
    public $facebook_share_body;
    
    /**
     * @form.field.type             image
     * @form.field.use_id
     * @form.field.display_image
     * @form.field.label            Facebook Share Image
     * @form.field.group            facebook_share
     */
    public $facebook_share_image;
    
    /**
     * @form.field.type             wysiwyg
     * @form.field.label            Official Rules
     * @form.field.group            rules
     */
    public $rules;
    
    /**
     * @form.field.type             image
     * @form.field.label            Banner Image (810px)
     * @form.field.group            images
     * @form.field.display_image
     * @form.field.use_id
     * @campaign.tab                display
     */
    public $image810;
    
    /**
     * @form.field.type             image
     * @form.field.label            Banner Image (520px)
     * @form.field.group            images
     * @form.field.display_image
     * @form.field.use_id
     * @campaign.tab                display
     */
    public $image520;
    
    /**
     * @form.field.type             image
     * @form.field.label            Banner Image (400px)
     * @form.field.group            images
     * @form.field.display_image
     * @form.field.use_id
     * @campaign.tab                display
     */
    public $image400;
    
    /**
     * @form.field.type             wysiwyg
     * @form.field.label            Form Intro Text
     * @form.field.hide_label       false
     * @form.field.group            messages
     * @campaign.tab                display
     */
    public $intro;
    
    /**
     * @form.field.type             wysiwyg
     * @form.field.label            Details Page
     * @form.field.hide_label       false
     * @form.field.group            messages
     * @campaign.tab                display
     */
    public $details_page;
    
    /**
     * @form.field.type             wysiwyg
     * @form.field.label            Thank you Body
     * @form.field.hide_label       false
     * @form.field.group            messages
     * @campaign.tab                display
     */
    public $thankyou;
    
    /**
     * @form.field.type             wysiwyg
     * @form.field.label            Before Start Message
     * @form.field.hide_label       false
     * @form.field.group            messages
     * @campaign.tab                display
     */
    public $before_start_message;
    
    /**
     * @form.field.type             wysiwyg
     * @form.field.label            After End Message
     * @form.field.hide_label       false
     * @form.field.group            messages
     * @campaign.tab                display
     */
    public $after_end_message;
    
    /**
     * @form.field.type             textarea
     * @form.field.label            Public Key
     * @form.field.hide_label       false
     * @form.field.group            encryption
     * @campaign.tab                encryption
     */
    public $openssl_public_key;
    
    /**
     * @form.field.type             text
     * @form.field.label            Encryption Creator
     * @form.field.hide_label       false
     * @form.field.group            encryption
     * @campaign.tab                encryption
     */
    public $openssl_creator;
    
    /**
     * @form.field.type             text
     * @form.field.label            Encryption Create Date
     * @form.field.hide_label       false
     * @form.field.group            encryption
     * @campaign.tab                encryption
     */
    public $openssl_create_date;
    
    public function getOptions( $name )
    {
        $options = array();
        switch( $name ){
            case 'notifications':
              $options = array(
                ''        => 'Disabled',
                'daily'   => 'Daily'
              );
              break;
            case 'timezone':
                foreach( timezone_identifiers_list() as $timezone ){
                    if ( !preg_match( '/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $timezone ) ) continue;
                    $options[$timezone] = $timezone;
                }
                break;
            case 'likeGate':
                $options = array(
                    '0' => 'Disabled',
                    '1' => 'Facebook Tab Only',
                    '2' => 'Always'
                );
                break;
            case 'facebookConnect':
                $options = array(
                    '0' => 'Disabled',
                    '1' => 'Enabled'
                );
                break;
        }
        return $options;
    }
}