<?php

/**
 * @wp.posttype.name                    sweep_campaign
 * @wp.posttype.single                  Campaign
 * @wp.posttype.plural                  Campaigns
 *
 * @wp.posttype.supports.editor         true
 *
 */
class Sweeps_Campaign extends Snap_Wordpress_PostType
{
    
    protected $form;
    protected $entry_form;
    protected $entry_id;
    protected $_facebook;
    protected $_facebookTab = false;
    protected $_facebookPage;
    protected $_facebookLiked=false;
    protected $_facebookAdmin=false;
    protected $success=false;
    protected $_ageRestricted=false;
    
    public function __construct()
    {
        parent::__construct();
        $this->form = new Sweeps_Campaign_Form;
        do_action('sweep_campaign_init');
        add_action('sweep_enter', array(&$this, 'sweep_enter'));
    }
    
    public function get_success()
    {
        return $this->success;
    }
    
    public function get_entry_id()
    {
        return $this->entry_id;
    }
    
    public function get_form()
    {
        static $loaded=false;
        if( !$loaded ){
            global $post;
            if( !$post || !@$post->ID ) return $this->form;
            $this->form->loadMeta( get_the_ID() );
            $loaded = true;
        }
        return $this->form;
    }
    
    public function _wp_add_meta_box( $id, $title, $callback, $post_type, $context, $priority )
    {
        $the_tab = $this->snap->method($id, 'campaign.tab', 'details');
        if( $the_tab != 'all' && $this->get_tab() != $the_tab ) return;
        
        if( !$post_type ) $post_type = $this->name;
        add_meta_box( $id, $title, $callback, $post_type, $context, $priority );
    }
    
    protected function get_tabs()
    {
        $tabs = array(
            'details'   => 'Details',
            'facebook'  => 'Facebook',
            // 'form'      => 'Form',
            'display'   => 'Display',
            'prizes'    => 'Prizes'
        );
        return apply_filters('sweeps_campaign_tabs', $tabs);
    }
    
    public function get_tab()
    {
        $tab = @$_REQUEST['tab'];
        if( !in_array($tab, array_keys( $this->get_tabs() ) ) ) $tab = 'details';
        return $tab;
    }
    
    protected function is_tab( $name )
    {
        return $this->get_tab() == $name;
    }
    
    /**
     * Detect which tab we were on during save POST requests
     *
     * @wp.filter
     */
    public function redirect_post_location( $location, $post_id )
    {
        if( get_post_type($post_id) == $this->name ){
            $location = add_query_arg('tab', @$_REQUEST['tab'], $location );
        }
        return $location;
    }
    
    /**
     * @wp.action               add_meta_boxes
     */
    public function add_tabs( $post_type )
    {
        if( $post_type !== $this->name ) return;
        // sneaky navigation
        global $form_extra;
        $tab = $this->get_tab();
        ob_start();
        ?>
        <input type="hidden" name="tab" value="<?= $tab ?>" />
        <h2 class="nav-tab-wrapper campaign-tabs">
            <? foreach( $this->get_tabs() as $key => $label ){
                $url = add_query_arg( 'tab', $key );
                $classes = array('nav-tab');
                if( $this->is_tab( $key ) ) $classes[] = 'nav-tab-active';
                ?>
            <a href="<?= $url ?>" class="<?= implode(' ', $classes) ?>"><?= $label ?></a>
            <?php } ?>
        </h2>
        <?php
        $form_extra .= ob_get_clean();
        $tabFn = 'tab_'.$tab;
        if( method_exists( $this, $tabFn) ){
            $form_extra .= $this->$tabFn();
        }
    }
    
    protected function filterArgs( $args )
    {
        #$args['menu_icon'] = SWEEPS_URL.'/resources/images/bozuko-icon.png';
        return $args;
    }
    
    public function facebook()
    {
        if( !isset($this->_facebook) ) $this->_facebook = new Facebook( array(
            'appId'     => get_post_meta( get_the_ID(), 'facebook_id', true ),
            'secret'    => get_post_meta( get_the_ID(), 'facebook_secret', true )
        ));
        
        return $this->_facebook;
    }
    
    public function getTime( $which='start' ){
        
        $str =   get_post_meta( get_the_ID(), $which.'_date', true )
               . ' '
               . get_post_meta( get_the_ID(), $which.'_time', true );
        
        return new DateTime( $str, new DateTimeZone( $this->getValue('timezone') ) );
    }
    
    public function getStart()
    {
        return $this->getTime('start');
    }
    public function getEnd()
    {
        return $this->getTime('end');
    }
    
    public function isActive()
    {
        return !$this->isBeforeStart() && !$this->isAfterEnd();
    }
    
    public function isBeforeStart()
    {
        $time = time();
        return $time < (int)$this->getStart()->format('U');
    }
    public function isAfterEnd()
    {
        $time = time();
        return $time > (int)$this->getEnd()->format('U');
    }
    public function isAgeRestricted()
    {
        return $this->_ageRestricted || @$_COOKIE['sweep_age_restriction'];
    }
    public function getValue( $key, $post_id=null )
    {
        static $values = array();
        if( !isset($values[$key]) ){
            $values[$key] = get_post_meta( $post_id ? $post_id : get_the_ID(), $key, true );
        }
        return $values[$key];
    }
    
    /**
     * @wp.action           wp
     * @wp.priority         1
     */
    public function facebook_init()
    {
        if( is_admin() ) return;
        $method = strtolower( $_SERVER['REQUEST_METHOD'] );
        // the "page" property indicates facebook tab
        if( $method == 'post' && ($sr = $this->facebook()->getSignedRequest()) && @$sr['page'] ){
            $_SESSION['facebook_page'] = $this->_facebookPage = $sr['page']['id'];
            $_SESSION['facebook_liked'] = $this->_facebookLiked = $sr['page']['liked'];
            $_SESSION['facebook_admin'] = $this->_facebookAdmin = $sr['page']['admin'];
            $url = add_query_arg('facebook_tab', '1');
            $app_data = @$sr['app_data'];
            if( $app_data && ($data = json_decode($app_data)) && ($s =@$data->shortcut ) ){
                $url = add_query_arg('shortcut', $s, $url);
            }
            
            $url = add_query_arg('sr', base64_encode(json_encode($sr)), $url);
            wp_redirect( $url );
            exit;
        }
        
        else if( @$_GET['facebook_tab'] ){
            
            $sr = @$_GET['sr'];
            if( $sr ) $sr = json_decode( base64_decode( $sr), true );
            else $sr = array('page'=>array('liked'=>0,'id'=>null,'admin'=>0));
            
            $this->_facebookTab = true;
            $this->_facebookPage = isset( $sr['page'] ) && isset( $sr['page']['id'] ) ? $sr['page']['id'] : @$_SESSION['facebook_page'];
            $this->_facebookLiked = isset( $sr['page'] ) && isset( $sr['page']['liked'] ) ? $sr['page']['liked'] : @$_SESSION['facebook_liked'];
            $this->_facebookAdmin = isset( $sr['page'] ) && isset( $sr['page']['admin'] ) ? $sr['page']['admin'] : @$_SESSION['facebook_admin'];
            
            if( @$_GET['sr'] ){
                $_SESSION['facebook_page'] = $this->_facebookPage;
                $_SESSION['facebook_liked'] = $this->_facebookLiked;
                $_SESSION['facebook_admin'] = $this->_facebookAdmin;
            }
            
        }
        
        elseif( $this->isFacebookConnect() && ($user = $this->getFacebookUser())){
            $fbid = $this->get_form()->field('facebookPageId')->getValue();
            $result = $this->facebook()->api('/fql', array(
                'q' => 'SELECT page_id FROM page_fan WHERE uid=me() AND page_id = '.$fbid
            ));
            $this->_facebookLiked  = $result && is_array( $result ) && count( @$result['data'] );
        }
    }
    
    public function isFacebookTab()
    {
        return $this->_facebookTab;
    }
    
    public function isFacebookLiked()
    {
        return $this->_facebookLiked;
    }
    
    public function isFacebookLikeGated()
    {
        $v = $this->get_form()->field('likeGate')->getValue();
        return $v == 2 || ($v==1 && $this->isFacebookTab());
    }
    
    public function isFacebookConnect()
    {
        return $this->get_form()->field('facebookConnect')->getValue();
    }
    
    public function getFacebookUser()
    {
        // test to make sure we have a user...
        try{
            $data = $this->facebook()->api('/me');
            return $data && @$data['id'] ? $data : false;
        }
        catch (FacebookApiException $e) {
            $this->facebook()->destroySession();
            return false;
        }
    }
    
    /**
     * @wp.action
     */
    public function manage_posts_custom_column( $column )
    {
        global $post;
        if( $post->post_type != $this->name ) return;
        switch( $column ) {
            case 'entries':
                // need to get the count
                $query = self::get_entries($post);
                echo '<a href="edit.php?post_type=sweep_entry&amp;campaign='.$post->ID.'"><strong>'.
                    $query->found_posts.'</strong></a>';
                echo
                    '<div class="row-actions">'.
                        '<span><a href="edit.php?post_type=sweep_entry&amp;campaign='.$post->ID.'">View</a> | </span>'.
                        '<a href="?sweep_download_entries='.$post->ID.'">Download</a>'.
                    '</div>';
                wp_reset_postdata();
                break;
        }
    }
    
    /**
     * @wp.filter
     */
    public function post_row_actions( $actions, $post )
    {
        if( get_post_type( $post ) !== $this->name ) return $actions;
        unset( $actions['inline hide-if-no-js']);
        // add our tabs
        foreach( $this->get_tabs() as $key=> $label){
            if( $key == 'details' ) continue;
            $actions[$key] = '<a href="post.php?post='.$post->ID.'&amp;action=edit&amp;tab='.$key.'">'.$label.'</a>';
        }
        return $actions;
    }
    
    /**
     * @wp.filter                   manage_edit-sweep_campaign_columns
     */
    public function entry_columns( $columns )
    {
        $date = $columns['date'];
        unset($columns['date']);
        $columns['entries'] = 'Entries';
        $columns['date'] = $date;
        return $columns;
    }
    
    public function get_entries( $post=null )
    {
        static $query = array();
        $id = $post ? $post->ID : get_the_ID();
        if( !isset($query[$id])){
            $query[$id] = new WP_Query(array(
                'post_type'     => 'sweep_entry',
                'meta_query'    => array(
                    array(
                        'key'       => 'campaign',
                        'value'     => $id
                    )
                )
            ));
        }
        $query[$id]->rewind_posts();
        return $query[$id];
    }
    
    public function get_prizes( $id=null )
    {
        return Snap::inst('Sweeps_Prize')->get_campaign_prizes( $id ? $id : get_the_ID() );
    }
    
    public function get_all()
    {
        static $campaigns;
        if( !isset($campaigns) ){
            $campaigns = new WP_Query(array(
                'post_type'     => $this->name
            ));
        }
        return $campaigns;
    }
    
    /**
     * @wp.action
     */
    public function add_meta_boxes( $post_type, $post )
    {
        if( $post_type !== $this->name ) return;
        $this->form->loadMeta( $post->ID );
        // also lets add the date picker
        Snap_Wordpress_Util::datepicker();
        // we can remove the post_type support for editor
        remove_post_type_support( $this->name, 'editor');
    }
    
    /**
     * @wp.meta_box
     * @wp.title            Campaign Details
     */
    public function meta_box_publish( $post )
    {
        $this->form->render(array(
            'group'             => 'publishing',
            'renderer'          => 'Snap_Wordpress_Form_Renderer_AdminTable'
        ));
    }
    
    /**
     * @wp.meta_box
     * @wp.title            Facebook Application
     * @campaign.tab        facebook
     */
    public function meta_box_facebook_app( $post )
    {
        $this->form->render(array(
            'group'             => 'facebook_app',
            'renderer'          => 'Snap_Wordpress_Form_Renderer_AdminTable'
        ));
    }
    
    /**
     * @wp.meta_box
     * @wp.title            Facebook Share
     * @campaign.tab        facebook
     */
    public function meta_box_facebook_share( $post )
    {
        $this->form->render(array(
            'group'             => 'facebook_share',
            'renderer'          => 'Snap_Wordpress_Form_Renderer_AdminTable'
        ));
    }
    
    /**
     * @wp.meta_box
     * @wp.title                    Banners
     * @campaign.tab                display
     */
    public function meta_box_images( $post )
    {
        if( $post->post_type != $this->name ) return;
        $this->form->render(array(
            'group'             => 'images',
            'renderer'          => 'Snap_Wordpress_Form_Renderer_AdminTable'
        ));
    }
    
    /**
     * @wp.meta_box
     * @wp.title                    Form Body
     * @campaign.tab                display
     */
    public function meta_box_formcopy( $post )
    {
        $this->form->render(array(
            'group'             => 'messages',
            'renderer'          => 'Snap_Wordpress_Form_Renderer_AdminTable'
        ));
    }
    
    /**
     * @-wp.meta_box
     * @wp.title            Form
     * @campaign.tab        form
     */
    public function meta_box_form( $post )
    {
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('sortable');
        wp_enqueue_script('forminator', SWEEPS_URL.'/resources/js/forminator.js', array('jquery'));
        $form_config = get_post_meta( $post->ID, 'form_config', true );
        if( !$form_config || !is_array( $form_config ) ){
            $form_config = array(
                
            );
        }
        ?>
        <input type="hidden" name="form_config" value="<?= json_encode( $form_config ) ?>" />
        <script type="text/javascript">
        jQuery(function($){$('input[name=form_config]').forminator();});
        </script>
        <?php
    }
    
    /**
     * @wp.meta_box
     * @wp.title                    Official Rules     
     */
    public function meta_box_rules( $post )
    {
        if( $post->post_type != $this->name ) return;
        $this->form->render(array(
            'group'             => 'rules',
            'renderer'          => 'Snap_Wordpress_Form_Renderer_AdminTable'
        ));
    }
    
    /**
     * @wp.meta_box
     * @wp.title            Entries
     * @wp.context          side
     * @wp.priority         low
     * @campaign.tab        all
     */
    public function meta_box_entries( $post )
    {
        // count the number of entries
        global $wpdb;
        $id = get_the_ID();
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta m ON m.post_id = p.ID WHERE p.post_type = 'sweep_entry' AND p.post_status='publish' AND m.meta_key = 'campaign' AND m.meta_value = '$id';");
        ?>
        <h4 class="entry-count"><a href="edit.php?post_type=sweep_entry&campaign=<?= $id ?>"><?= $count ?></a></h4>
        <ul>
            <li><a href="edit.php?post_type=sweep_entry&campaign=<?= $id ?>">View Entries</a></li>
            <li><a href="?sweep_download_entries=<?= $id ?>">Download Entries</a></li>
        </ul>
        <?php
    }
    
    /**
     * @wp.meta_box
     * @wp.title                Add Prize
     * @campaign.tab            prizes
     */
    public function meta_box_prizes( $post )
    {
        
        $json = array();
        $newForm = new Sweeps_Prize_Form();
        $newForm->addField('ID', array('type'=>'hidden'));
        ?>
        <div class="prizes-container">
            <div class="new-form" style="display: none;">
                <div class="prize-form">
                    <div class="header"></div>
                    <div class="mainstuff">
                        <?
                        $newForm->render(array(
                            'renderer'          => 'Snap_Wordpress_Form_Renderer_AdminTable'
                        ));
                        ?>
                        <div style="text-align:right;">
                            <a href="#" class="button button-secondary remove-prize">Remove</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="the-prizes">
            <?php
            $prizes = Snap::inst('Sweeps_Prize')->get_campaign_prizes( $post->ID );
            #print_r($prizes->posts);
            while( $prizes->have_posts() ){
                $prize = $prizes->next_post();
                $form = new Sweeps_Prize_Form();
                $form->loadMeta( $prize->ID );
                $values = array_merge( (array)$prize, $form->getValues() );
                if( @$values['image'] ){
                    $src = wp_get_attachment_image_src( $values['image'], 'prize' );
                    $values['image_url'] = $src[0];
                }
                $json[] = $values;
            }
            ?>
            </div>
            <input type="hidden" name="prizes" value="<?= esc_attr(json_encode( $json )) ?>" />
            <a class="button button-primary add-prize">Add Another Prize</a>
            <noscript>
            Javascript must be enabled to add prizes.
            </noscript>
        </div>
        <script type="text/javascript" src="<?= SWEEPS_URL ?>/resources/js/prizes.js"></script>
        <?php
    }
    
    
    /**
     * @wp.meta_box
     * @wp.title            Sweepstakes Campaign
     * @wp.post_type        page
     */
    public function meta_box_page( $post )
    {
        global $post;
        $old = $post;
        $query = new WP_Query('post_type='.$this->name.'&posts_per_page=-1');
        $id = get_post_meta( $post->ID, 'sweeps_campaign', true );
        ?>
        <p>Select a campaign to display for this page. Please note, any content above will not be displayed, only the campaign.</p>
        <select name="sweeps_campaign">
            <option value="-1">No Campaign</option>
            <?php while( $query->have_posts() ){ $query->the_post(); ?>
            <option value="<?= get_the_ID() ?>" <? if( get_the_ID() == $id ) { ?>selected<? } ?>><? the_title() ?></option>
            <?php } ?>
        </select>
        <?php
        wp_reset_postdata();
        $post = $old;
    }
    
    /**
     * @wp.action           save_post
     */
    public function save_page( $page )
    {
        if( get_post_type( $page ) !== 'page' ) return;
        $id = @$_POST['sweeps_campaign'];
        update_post_meta( $page, 'sweeps_campaign', $id);
        get_option('sweep_pages');
        $sweep_pages = get_option('sweep_pages');
        if( !$sweep_pages ) $sweep_pages = array();
        $sweep_pages[ get_post($page)->post_name ] = $id;
        update_option('sweep_pages', $sweep_pages);
    }
    
    /**
     * @wp.filter           pre_get_posts
     */
    public function change_query( &$query )
    {
        $sweep_pages = get_option( 'sweep_pages' );
        $pagename = $query->get('pagename');
        $pageid = $query->get('page_id');
        
        $id = false;
        if( $pageid ) $id = get_post_meta( $pageid, 'sweeps_campaign', true);
        else if( $sweep_pages && isset( $sweep_pages[$pagename]) ) $id = $sweep_pages[$pagename];
        
        if( !$id || $id == -1 ) return;
        
        $query->set('pagename',     '' );
        $query->set('page_id',      '' );
        $query->set('post_type',    $this->name );
        $query->set('p',            $id );
        
    }
    
    /**
     * @wp.action           template_redirect
     */
    public function display_sweep()
    {
        if( get_post_type() !== $this->name ) return;
        
        global $post, $campaign;
        
        $this->form->loadMeta(  get_the_ID() );
        
        // wait a minute... should we pump them to the facebook page?
        $facebookTab = $this->form->field('facebookTabUrl')->getValue();
        $facebookCrawler = preg_match('/facebookexternalhit/i', @$_SERVER['HTTP_USER_AGENT'] );
        
        $facebookReferer = preg_match('/page_proxy\.php/', @$_SERVER['HTTP_REFERER'] );
        
        if( $facebookTab && !Snap_Util_Device::is_mobile() && !$this->isFacebookTab() && !$facebookCrawler){
            if( ($s=@$_REQUEST['shortcut']) ){
                $data = json_encode( array('shortcut'=>$s) );
                $facebookTab = add_query_arg('app_data', urlencode($data), $facebookTab );
            }
            wp_redirect( $facebookTab );
            exit;
        }
        
        $campaign = $post;
        foreach( $this->form->getValues() as $key => $value ){
            $post->$key = $value;
        }
        
        if( $this->isFacebookTab() || $this->isFacebookConnect() ){
            wp_enqueue_script('facebook-sdk', 'https://connect.facebook.net/en_US/all.js', array('jquery'));
        }
        
        wp_enqueue_script('jquery-cookie', SWEEPS_URL.'/template/js/jquery.cookie.js', array('jquery'));
        wp_enqueue_script('sweep-campaign', SWEEPS_URL.'/template/js/app.js', array('jquery'));
        // wp_enqueue_style('open-sans', 'https://fonts.googleapis.com/css?family=Open+Sans:400,700,800,600');
        // add our script config
        wp_localize_script('sweep-campaign','Sweep_Campaign', array(
            'is_facebook_tab'       => (bool)$this->_facebookTab,
            'is_before'             => $this->isBeforeStart(),
            'is_after'              => $this->isAfterEnd(),
            'is_active'             => $this->isActive(),
            'facebook_like_gate'    => $this->form->field('likeGate')->getValue(),
            'facebook_connect'      => (bool)$this->form->field('facebookConnect')->getValue(),
            'is_facebook_liked'     => (bool)$this->_facebookLiked,
            'facebook_page_id'      => $this->form->field('facebookPageId')->getValue(),
            'facebook_page_url'     => $this->form->field('facebookPageUrl')->getValue(),
            'facebook_id'           => $this->form->field('facebook_id')->getValue(),
            'channel_url'           => SWEEPS_URL.'/channel.html'
        ));
        do_action('before_display_sweep');
        Snap_Wordpress_Template::load('sweeps', 'campaign');
        exit;
    }
    
    /**
     * @wp.filter                       sweeps_entry_mask
     * /
    public function mask_before_start($masks)
    {
        if( !$this->isBeforeStart() ) return $masks;
        $masks[] = array(
            'id'        => 'inactive-before-start',
            'content'   => "This sweepstakes does not begin until ".$this->getStart()->format('F d, Y @ h:i A')
        );
        return $masks;
    }
    
    /**
     * @wp.filter                       sweeps_entry_mask
     * 
    public function mask_after_end($masks)
    {
        if( !$this->isAfterEnd() ) return $masks;
        $masks[] = array(
            'id'        => 'inactive-after-end',
            'content'   => "This sweepstakes has ended"
        );
        return $masks;
    }
     */
    
    /**
     * @wp.filter                       sweeps_entry_mask
     */
    public function mask_age_restricted($masks)
    {
        if( !$this->isActive() || !$this->isAgeRestricted() ) return $masks;
        $masks[] = array(
            'id'        => 'age-restriction',
            'content'   => 'Sorry, you are not eligible to enter.<div class="subtext">See <a href="#official-rules">Official Rules</a> below for details.</div>'
        );
        return $masks;
    }
    
    /**
     * @wp.filter                       sweeps_entry_mask
     */
    public function mask_facebook_connect($masks)
    {
        if( !$this->isActive() || !$this->isFacebookConnect() || $this->getFacebookUser() ) return $masks;
        $masks[] = array(
            'id'        => 'facebook-connect-mask',
            'classes'   => array('facebook-mask','facebook-connect-mask'),
            'content'   => 'Please connect your account<br />'
                          .'<a class="facebook-login"><span>Login with Facebook</span></a>'
                          .'<div class="subtext subtext-small">No purchase necessary.<br />See <a href="#official-rules">Official Rules</a> below for details.</div>'
        );
        return $masks;
    }
    
    /**
     * @wp.filter                       sweeps_entry_mask
     */
    public function mask_facebook_like($masks)
    {
        if( !$this->isActive() ) return $masks;
        if( $this->isFacebookTab() && !$this->isFacebookLiked() && $this->isFacebookLikeGated() ){
            $thumbup = SWEEPS_URL.'/template/images/thumbs-up.png';
            $masks[] = array(
                'id'        => 'facebook-like-mask',
                'classes'   => array('facebook-mask', 'facebook-like-mask'),
                'content'   => "<img src=\"$thumbup\" alt=\"Like us on Facebook\"/> Please Like Us to Enter"
                              .'<div class="subtext subtext-small">No purchase necessary.<br />See <a href="#official-rules">Official Rules</a> below for details.</div>'
            );
        }
        elseif( !$this->isFacebookTab() && !$this->isFacebookLiked() && $this->isFacebookLikeGated() ){
            
            $page_url = $this->get_form()->field('facebookPageUrl')->getValue();
            
            $masks[] = array(
                'id'        => 'facebook-connect-like-mask',
                'classes'   => array('facebook-mask'),
                'content'   => "Please Like Us to Enter.<br />".
                               '<div class="fb-like-ct"><div class="fb-like" data-href="'.$page_url.'" data-send="false" data-width="240" '
                              .'data-show-faces="true"></div></div>'
                              .'<div class="subtext subtext-small">No purchase necessary.<br />See <a href="#official-rules">Official Rules</a> below for details.</div>'
            );
        }
        return $masks;
    }
    
    /**
     * @wp.action
     */
    public function sweeps_after_body()
    {
        
        if( $this->isActive() && $this->isFacebookTab() && !$this->isFacebookLiked() && $this->isFacebookLikeGated() ){
            ?>
            <div class="facebook-arrow"></div>
            <?php
        }
    }
    
    /**
     * @wp.filter
     */
    public function body_class( $classes )
    {
        $classes[] = 'campaign';
        if( $this->isFacebookTab() ){
            $classes[] = 'facebook-tab';
        }
        return $classes;
    }
    
    /**
     * @wp.action
     */
    public function save_post( $post_id, $post )
    {
        if( get_post_type($post) !== $this->name ) return;
        $this->form->loadMeta( $post_id );
        if( $this->form->process( $_POST ) ){
            $this->form->updateMeta( $post_id );
        }
        
        $this->save_prizes();
    }
    
    protected function save_prizes()
    {
        $prizes_json = @$_POST['prizes'];
        if( !$prizes_json ) return;
        if( !($prizes = json_decode( $prizes_json )) ){
            $prizes = json_decode( stripslashes( $prizes_json ) );
        }
        if( !$prizes || !is_array( $prizes) ) return;
        $ids = array();
        
        
        foreach( $prizes as $index => $prize ){
            if( @$prize->ID ){
                $post = get_post( $prize->ID );
                $post->post_title = @$prize->name;
                $post->post_content = @$prize->description;
                wp_update_post( (array)$post );
            }
            else{
                $data = array(
                    'post_type'     => 'sweep_prize',
                    'post_status'   => 'publish',
                    'post_title'    => @$prize->name,
                    'post_content'  => @$prize->description
                );
                
                $id = wp_insert_post($data, true);
                $post = get_post( $id );
            }
            $prize->campaign = get_the_ID();
            $prize->order = $index+1;
            // update our meta
            
            
            $form = new Sweeps_Prize_Form();
            $form->addField('campaign', array('type'=>'hidden'));
            $form->addField('order');
            $form->setValues( (array)$prize );
            
            $form->updateMeta( $post->ID );
            $ids[] = $post->ID;
        }
        
        $q = new WP_Query(array(
            'post_type'         => 'sweep_prize',
            'meta_query'        => array(
                array(
                    'key'           => 'campaign',
                    'value'         => get_the_ID()
                )
            ),
            'post__not_in'      => $ids
        ));
        
        while( $q->have_posts() ) wp_delete_post( $q->next_post()->ID );
        
    }
    
    /**
     * @wp.shortcode
     */
    public function sweep( $attrs = array(), $content = '', $tag = '' )
    {
        // lets talk about this sweeps campaign
        extract( $attrs );
        if( !$id ){
            echo "No Sweep Specified";
            return;
        }
        $campaign = get_post( $id );
        if( !$campaign->post_type == $this->name ){
            echo "ID Specified is not a sweepstakes campaign";
            return;
        }
    }
    
    public function getEntryForm( $id=null )
    {
        if( !$this->entry_form ){
            $form = new Sweeps_Entry_Form;
            $this->entry_form = apply_filters('sweeps_init_form', $form, $id ? $id : get_the_ID() );
            
            // okay, lets go through and remove fields that don't need to be there
            foreach( $this->entry_form->getFieldNames() as $name ){
                $desktop = $this->entry_form->field( $name )->cfg('desktop');
                //echo "$name: $desktop<br />";
                if(
                   ($this->entry_form->field( $name )->cfg('desktop') && !Snap_Util_Device::is_desktop() )
                   ||
                   ($this->entry_form->field( $name )->cfg('mobile') && !Snap_Util_Device::is_mobile() )
                ){
                    $this->entry_form->removeField( $name );
                }
            }
            
            // we should have an id to associate with this...
            $id = get_the_ID();
            $this->entry_form->addField('nonce', array(
                'type' => 'hidden'
            ));
            $this->entry_form->addField('campaign', array(
                'type' => 'hidden'
            ));
            $this->entry_form->addField('campaign_submit', array(
                'type' => 'hidden'
            ));
            $this->entry_form->addField('shortcut', array(
                'type' => 'hidden'
            ));
            
            if( $this->entry_form->field('email') )
                $this->entry_form->field('email')->setCfg('validator.uniqueEmail', true);
                
            if( $this->entry_form->field('phone') )
                $this->entry_form->field('phone')->setCfg('validator.uniquePhone', true);
            
            $s = @$_REQUEST['shortcut'];
            if( $s ){
                global $wpdb;
                $s = base_convert( $s, 16, 10 );
                $post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts p JOIN $wpdb->postmeta m ON m.post_id = p.ID WHERE m.meta_value = '$s' AND m.meta_key = '_shortcut'");
                if( $post_id ){
                    $this->entry_form->loadMeta( $post_id );
                    $this->entry_form->field('shortcut')->setValue($s);
                }
            }
            
            if( !@$_POST['campaign_submit'] ) $this->entry_form->setValues(array(
                'nonce'             => wp_create_nonce('sweep-'.$id),
                'campaign'          => $id,
                'campaign_submit'   => 1
            ));
            
            $this->entry_form = apply_filters('sweeps_after_init_form', $this->entry_form);
        }
        return $this->entry_form;
    }
    
    public function open_form( $renderer )
    {
        $url = add_query_arg('tmp', false);
        if( $this->isFacebookTab() ){
            $url = add_query_arg('facebook_tab', 1, $url);
        }
        $form = $this->getEntryForm();
        $renderer->renderOpenForm( $url );
        $renderer->renderField( $form->field('nonce') );
        $renderer->renderField( $form->field('campaign') );
        $renderer->renderField( $form->field('campaign_submit') );
        $renderer->renderField( $form->field('shortcut') );
    }
    
    /**
     * @wp.action           wp
     * @wp.priority         2
     */
    public function init_page()
    {
        if( is_admin() || get_post_type() != $this->name ) return;
        
        // register theme path
        global $post;
        $path = get_stylesheet_directory().'/sweeps_'.$post->post_name;
        Snap_Wordpress_Template::registerPath('sweeps', $path );
        
        // load any functions
        Snap_Wordpress_Template::inc('sweeps', 'index');
        
    }
    
    public function register_template( $post )
    {
        // register theme path
        if( $post ) $path = get_stylesheet_directory().'/sweeps_'.$post->post_name;
        Snap_Wordpress_Template::registerPath('sweeps', $path );
        
        // load any functions
        Snap_Wordpress_Template::inc('sweeps', 'index');
    }
    
    /**
     * @wp.action           wp
     * @wp.priority         10
     */
    public function enter()
    {
        
         
        if( @$_COOKIE['sweep_age_restriction'] ){
            $this->_ageRestricted = true;
            do_action('sweep_age_restriction');
            return;
        }
        
        if( @$_SESSION['sweep_success'] ){
            $this->success = true;
            $this->entry_id = @$_SESSION['sweep_entry'];
            unset( $_SESSION['sweep_success'] );
            unset( $_SESSION['sweep_entry'] );
            return;
        }
        
        // check to see for the entry field
        if( !@$_POST['campaign_submit'] ) return;
        $form = $this->getEntryForm();
        $post = apply_filters('enter_campaign_post', $_POST);
        $form->setValues( $post, true );
        $campaign = @$_POST['campaign'];
        
        // check the nonce
        $nonce = @$_POST['nonce'];
        if( !$campaign || !wp_verify_nonce($nonce, 'sweep-'.$campaign) ){
            $form->addFormError("There was an error entering you into this sweepstakes.");
            return;
        }
        
        if( $this->isBeforeStart() ){
            $form->addFormError("Hold your horses. This sweepstakes does not begin until ".$start->format('m/d/Y h:i a'));
            return;
        }
        
        if( $this->isAfterEnd() ){
            $form->addFormError("Sorry, this sweepstakes has ended.");
            return;
        }
        
        if( !$form->process() ) return;
            
        if( ($birthday = $form->field('birthday')->getValue()) && $this->tooYoung($birthday) ) {
            $this->_ageRestricted = true;
            setcookie('sweep_age_restriction', true, apply_filters('sweeps_age_restriction_cookie_expiration', time()+60*60*24*1));
            $form->addFormError("Sorry, but you must be at least $age to enter this sweepstakes.");
            do_action('sweep_age_restriction');
            return;
        }
        
        $post_args = array(
            'post_title'    => strtolower(trim($form->field('email')->getValue())),
            'post_type'     => 'sweep_entry',
            'post_status'   => 'publish'
        );
        
        $id = wp_insert_post( $post_args, true);
        
        if( is_wp_error( $id ) ){
            // uh-oh... what happened?
            $form->addFormError( $id->message );
            return;
        }
        
        // check for a shortcut
        if( ($shortcut = $form->field('shortcut')->getValue()) ){
            // not sure if we really need to do anything else here.
        }
        
        foreach( $form->getValues() as $key => $value ){
            update_post_meta( $id, $key, $value );
        }
        
        // add some custom stuff too
        update_post_meta( $id, 'IP', $_SERVER['REMOTE_ADDR'] );
        update_post_meta( $id, 'sweep_id', $_POST['campaign'] );
        update_post_meta( $id, 'verified', true );
        
        if( ($user = $this->getFacebookUser()) ){
            update_post_meta( $id, 'facebook_id', $user['id'] );
        }
        
        $this->success = true;
        $this->entry_id = $id;
        
        // anyone want to do something?
        do_action('sweep_enter', $id, $campaign );
        
        // we want to display the thank you...
        $_SESSION['sweep_success'] = true;
        $_SESSION['sweep_entry'] = $id;
        wp_redirect($_SERVER['REQUEST_URI']);
        exit;
    }
    
    public function tooYoung( $formValue )
    {
        // check the birthday
        $tooYoung = false;
        $age = get_post_meta( get_the_ID(), 'ageRestriction', true );
        if( !$age ) return $tooYoung;
        $age        = (int) $age;
        $birthday   = new DateTime( $formValue );
        $tooYoung   = false;
        
        $year       = (int)$birthday->format('Y');
        $month      = (int)$birthday->format('m');
        $day        = (int)$birthday->format('d');
        
        $now        = new DateTime( null, new DateTimeZone( $this->getValue('timezone') ) );
        $now        = apply_filters('sweeps_age_from', $now);
        
        $nowYear    = (int)$now->format('Y');
        $nowMonth   = (int)$now->format('m');
        $nowDay     = (int)$now->format('d');
        
        $theYear    = $nowYear - $age;
        
        if( $year > $theYear ) $tooYoung = true;
        
        else if( $year == $theYear ){
            
            if( $nowMonth < $month ){
                $tooYoung = true;
            }
            else if( $nowMonth == $month && $nowDay < $day ){
                $tooYoung = true;
            }
        }
        
        return apply_filters('sweeps_age_restriction', $tooYoung, $formValue, $age );
    }
    
    /**
     * @wp.action           wp
     * @wp.priority         20
     */
    public function sweep_enter()
    {
        
        // check for ajax header
        $ajax = strcasecmp( @$_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0 || isset($_POST['_ajax_']);
        
        if( !$ajax ) return;
        
        if( $this->success ){
            ob_start();
            Snap_Wordpress_Template::load('sweeps', 'includes/thankyou');
            $html = ob_get_clean();
            
            return $this->returnJSON(array(
                'success'   => true,
                'entry_id'  => $this->entry_id,
                'html'      => $html
            ));
        }
        
        // check to see for the entry field
        if( !@$_POST['campaign_submit'] ) return;
        
        if( @$_COOKIE['sweep_age_restriction'] ){
            return $this->returnJSON(array(
                'success'           => false,
                'age_restriction'   => true
            ));
        }
        
        return $this->returnJSON(array(
            'success'           => false,
            'errors'            => $this->getEntryForm()->getErrors()
        ));
    }
    
    /**
     * @wp.action
     */
    public function sweep_age_restriction()
    {
        
        // check for ajax header
        if( @$_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' ) return;
        
        return $this->returnJSON(array(
            'success'           => false,
            'age_restriction'   => true
        ));
    }
    
    /**
     * @wp.action               wp
     * @wp.priority             3
     */
    public function receive_sms()
    {
        global $wpdb;
        
        // check for the protexting params
        $keyword = @$_GET['keyword'];
        if( !$keyword ) return;
        
        $this->form->loadMeta( get_the_ID() );
        $_keyword = $this->form->field('proTextingKeyword')->getValue();
        $_username = $this->form->field('proTextingUsername')->getValue();
        $_password = $this->form->field('proTextingPassword')->getValue();
        
        if( !$_keyword || !$_username || !$_password || strcasecmp( $_keyword, $keyword ) != 0 ) return;
        
        $number = $_GET['number'];
        $filtered_number = preg_replace('#^1#', '', $number);
        
        // already in there?
        $exists = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts p JOIN $wpdb->postmeta m ON p.ID = m.post_id WHERE m.meta_key = 'number' AND m.meta_value = '$filtered_number'");
        if( $exists ){
            // does this have a shortcut ? 
        }
        
        $id = wp_insert_post( array(
            'post_title'    => 'Phone: '.$number,
            'post_type'     => 'sweep_entry'
        ));
        
        update_post_meta( $id, 'phone', $filtered_number );
        
        
        //$wpdb->show_errors( true );
        $wpdb->query("
            INSERT INTO $wpdb->postmeta(post_id, meta_key, meta_value)
                VALUES( $id, '_shortcut',
                    (SELECT IFNULL( (SELECT MAX( m.meta_value+0 ) FROM $wpdb->postmeta m
                        WHERE m.meta_key = '_shortcut'), 0) + 1
                    )
                );
        ");
        
        $shortcut = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $id AND meta_key = '_shortcut'");
        
        $url = $this->form->field('productionUrl')->getValue();
        
        if( !$url ){
            // if this is related to a page
            $url = get_permalink();
        }
        
        $message = $this->form->field('smsReply')->getValue();
        if( !$message ){
            $message = '%url%';
        }
        
        $message = str_replace(
            array('%shortcut%', '%url%'),
            array($this->getShortcut($shortcut), $url.'?shortcut='.$this->getShortcut($shortcut)),
            $message
        );
        
        $ch=curl_init('https://api.protexting.com/sendmessage.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'user'      => $_username,
            'password'  => $_password,
            'number'    => $number,
            'message'   => $message
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        
        /* result of API call*/
        //wp_mail('fabrizim@owlwatch.com', $message, $message);
        exit;
        
    }
    
    public function getShortcut( $num )
    {
        return str_pad( base_convert( $num+0, 10, 16 ), 3, '0', STR_PAD_LEFT );
    }
    
    /**
     * @wp.action               admin_init
     */
    public function download_entries()
    {
        $campaign = @$_REQUEST['sweep_download_entries'];
        $download_all = @$_REQUEST['action'] === 'download_all_entries';
        if( !$campaign && !$download_all ) return;
        
        $name = "All_Entries";
        
        if( $campaign ){
            $campaign = get_post( $campaign );
            if( !$campaign  || !$campaign ->post_type == 'sweep_campaign'){
                print "No Campaign";
                exit;
            }
            $name = addslashes($campaign->post_title);
        }
        
        $this->register_template( $campaign );
        
        global $wpdb;
        
        $q = "
            SELECT DISTINCT(meta.meta_key)
            FROM $wpdb->postmeta meta
            JOIN $wpdb->posts post
                ON post.ID = meta.post_id
            WHERE post.post_type = 'sweep_entry'
        ";
        $distinct = $wpdb->get_results($q);
        
        $ignore = array('nonce', 'submit_form', 'campaign_submit');
        $keys = array();
        
        foreach( $distinct as $row ){
            $key = $row->meta_key;
            if( !in_array( $key, $ignore ) && substr($key,0,1)!='_' ) $keys[] = $key;
        }
        
        header('Content-Type: text/csv');
        header("Cache-Control: no-store, no-cache");
        header('Content-Disposition: attachment; filename="'.$name.'.csv"');
        
        $where = array(
            "post.post_type = 'sweep_entry'"
        );
        
        $groupby = '';
        
        if( $campaign ){
            $where[] = "meta.meta_key = 'campaign'";
            $where[] = "meta.meta_value = '$campaign->ID'";
        }
        
        else {
            $where[] = "meta.meta_key = 'email'";
            $groupby = "GROUP BY meta.meta_value";
        }
        
        $where = "WHERE ".implode(" AND ", $where);
        $query = "
            SELECT
                post.*
                
            FROM
                $wpdb->posts post
            JOIN
                $wpdb->postmeta meta
            ON
                post.ID = meta.post_id
                
            $where
            $groupby
            
            ORDER BY
                post.post_date DESC
        ";
        
        $result = mysql_query( $query, $wpdb->dbh );
        
        // meta keys
        $stdout = fopen('php://output', 'w');
        
        
        // headers
        $headers = array_merge( array('date'), $keys );
        $delimiter = apply_filters('sweep_csv_delimiter', ',');
        fputcsv( $stdout, $headers, $delimiter );
        
        while( ($post = mysql_fetch_object( $result )) ){
            
            $columns = array(
                $post->post_date
            );
            
            foreach( $keys as $key ){
                $columns[] = get_post_meta( $post->ID, $key, true);
            }
            
            fputcsv( $stdout, $columns, $delimiter );
            unset( $post );
        }
        fclose( $stdout );
        exit;
    }
    
    /**
     * TODO - write this function
     *
     * @wp.action               sweep_enter
     */
    public function send_thank_you_email()
    {
        
    }
    
    /**
     * @wp.filter
     */
    public function the_rules( $rules )
    {
        // replacers...
        return apply_filters('the_content', $rules);
        
    }
    
    public function intro()
    {
        if( $this->success ) return;
        return apply_filters('intro', $this->get_form()->field('intro')->getValue() );
    }
    
    public function isSuccess()
    {
        return $this->success;
    }
    
    public function entryForm( $renderer='Snap_Wordpress_Form_Renderer_Default' )
    {
        if( $this->success ){
            echo apply_filters('sweep_thank_you', $this->get_form()->field('thankyou')->getValue() );
            return;
        }
        
        Snap_Wordpress_Template::load('sweeps', 'includes/form');
    }
    
    public function thankYou()
    {
        echo apply_filters('sweep_thank_you', $this->get_form()->field('thankyou')->getValue());
        return;
    }
    
    public function rules()
    {
        global $campaign;
        $rules = $campaign->rules;
        echo apply_filters( 'the_rules', apply_filters( 'the_content', $rules ) );
    }
    
    public function get_google_analytics_code()
    {
        return $this->form->field('googleAnalyticsCode')->getValue();
    }
    
    /**
     * @wp.action               admin_init
     */
    public function load_sample_data()
    {
        if( @$_REQUEST['action'] != 'load_sample_data' ) return;
        global $wpdb;
        
        $sample = array(
            'first_name'    => array('Mark', 'John', 'Andrew', 'Layla', 'Chowda', 'Chevy', 'Pin-bo'),
            'last_name'     => array('Fabrizio', 'Epstein', 'Stone', 'Tsai'),
            'address'       => array('1 Betty Lane', '74 West St.', '5 Cushing St'),
            'city'          => array('Westford', 'Chelmsford', 'Andover', 'Medford', 'Boston'),
            'state'         => array('MA'),
            'postal_code'   => array('01852', '00234', '02412', '42352', '90210'),
            'birthday'      => array('1980-08-14 00:00:00', '1969-02-19 00:00:00')
        );
        
        $q = new WP_Query(array(
            'post_type'         => 'sweep_campaign',
            'posts_per_page'    => 1
        ));
        
        if( !$q->found_posts ) die('no campaigns');
        
        $campaign = $q->posts[0]->ID;
        
        $day = new DateTime('2012-01-04');
        $now = new DateTime();
        $num = (int)$wpdb->get_var("SELECT MAX(ID) FROM $wpdb->posts") + 1;
        while( $day <= $now ){
            $count = rand(0, 50);
            $start = $day->format('U');
            $day->modify('+1 day');
            $end = $day->format('U');
            for($i=0; $i<$count; $i++){
                $data = array();
                foreach( array_keys($sample) as $key ){
                    $data[$key] = $sample[$key][rand(0, count($sample[$key])-1)];
                }
                $data['email'] = "dude-$num@sample.com";
                $data['verified'] = true;
                $data['campaign'] = $campaign;
                $date = date('Y-m-d H:i:s', rand(+$start, $end-1));
                $id = wp_insert_post(array(
                    'post_status'   => 'publish',
                    'post_title'    => $data['email'],
                    'post_date'     => $date,
                    'post_type'     => 'sweep_entry'
                ));
                foreach($data as $key => $val){
                    update_post_meta( $id, $key, $val );
                }
                $num++;
            }
        }
        die("Loaded $num sample records");
    }
    
}