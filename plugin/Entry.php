<?php

/**
 * @wp.posttype.name                    sweep_entry
 * @wp.posttype.single                  Entry
 * @wp.posttype.plural                  Entries
 *
 * @wp.posttype.args.public             false
 * @wp.posttype.args.publicly_queryable false
 * @wp.posttype.args.show_ui            true
 *
 * @wp.posttype.supports.editor         false
 */
class Sweeps_Entry extends Snap_Wordpress_PostType
{
    
    public function __construct()
    {
        parent::__construct();
        $this->form = new Sweeps_Entry_Form();
    }
    
    protected function filterArgs( $args )
    {
        $args['supports'] = array('nothing');
        return $args;
    }
    
    /**
     * @wp.filter
     */
    public function pre_get_posts( &$query )
    {
        if( !is_admin() ) return;
        global $pagenow;
        if( $pagenow != 'edit.php' || $query->get('post_type') != 'sweep_entry' ) return;
        
        $campaign = @$_REQUEST['campaign'];
        if( !$campaign ) return;
        $meta_query = $query->get('meta_query');
        $meta_query = array(
            'key'       => 'campaign',
            'value'     => $campaign,
            'compare'   => '='
        );
        $query->set('meta_query', array(
            array(
                'key'           => 'campaign',
                'value'         => $campaign
            )
        ));
        
        
        return $query;
    }
    
    /**
     * @wp.filter               request
     */
    public function sortable_vars( $vars )
    {
        // check for orderby
        $orderby = @$vars['orderby'];
        $order = @$vars['order'];
        if( $orderby ){
            switch($orderby){
                case 'title':
                    $orderby = 'email';
                case 'last_name':
                case 'first_name':
                case 'postal_code':
                case 'email':
                    $vars = array_merge( $vars, array(
                        'meta_key'      => $orderby,
                        'orderby'       => 'meta_value'
                    ));
                    break;
            }
        }
        return $vars;
    }
    
    /**
     * @wp.action
     */
    public function restrict_manage_posts()
    {
        
        if( get_query_var('post_type') != 'sweep_entry' ) return;
        $campaign = @$_REQUEST['campaign'];
        // lets get a list of all campaigns
        $query = new WP_Query(array(
            'post_type'             => 'sweep_campaign',
            'posts_per_page'        => -1
        ));
        wp_reset_postdata();
        if( !$query->have_posts() ) return;
        ?>
        <select name="campaign">
            <? while( $query->have_posts() ){
                $query->the_post();
                $selected = false;
                if($campaign == get_the_ID() ){
                    $selected = true;
                    $campaign_title = get_the_title();
                }
                ?>
            <option value="<?= get_the_ID() ?>" <? if($selected){ ?>selected<? } ?>><?= get_the_title() ?></option>
            <?php } ?>
        </select>
        <?php
        
        // okay, now some jquery hacking...
        ?>
        <script type="text/javascript">
        jQuery(function($){
            $('.view-switch').prepend('<a class="button" style="position: relative; top: -5px; margin-right: 10px;" href="?sweep_download_entries=<?= $campaign ?>">Download</a>');
            $('.wrap>h2').html('Entries: <?= addslashes( $campaign_title ) ?> <a class="add-new-h2" href="post.php?post=<?= $campaign ?>&amp;action=edit">Edit Campaign</a>');
        });
        </script>
        <?php
    }
    
    /**
     * @wp.action
     */
    public function manage_posts_custom_column( $column )
    {
        global $post;
        if( $post->post_type != $this->name ) return;
        switch( $column ) {
            case 'email':
            case 'last_name':
            case 'first_name':
            case 'postal_code':
                echo get_post_meta( $post->ID, $column, true );
                break;
            case 'title':
                echo get_post_meta( $post->ID, 'email', true );
                break;
        }
    }
    
    /**
     * @wp.filter                   manage_edit-sweep_entry_sortable_columns
     */
    public function sortable_columns( $columns )
    {
        $columns['email']       = 'email';
        $columns['last_name']   = 'last_name';
        $columns['first_name']  = 'first_name';
        $columns['postal_code'] = 'postal_code';
        $columns['winner'] = 'winner';
        
        return $columns;
    }
    
    /**
     * @wp.filter                   manage_edit-sweep_entry_columns
     */
    public function entry_columns( $columns )
    {
        $entry_columns = array(
            'cb'            => $columns['cb'],
            'title'         => 'Email',
            'last_name'     => 'Last Name',
            'first_name'    => 'First Name',
            'postal_code'   => 'Postal Code',
            'winner'        => 'Winner',
            'date'          => 'Date'
        );
        return $entry_columns;
    }
    
    /**
     * @wp.action
     */
    public function add_meta_boxes( $post_type, $post )
    {
        if( $post_type !== $this->name ) return;
        $this->form->loadMeta( $post->ID );
    }
    
    /**
     * @wp.action
     */
    public function save_post( $post_id, $post )
    {
        if( get_post_type($post) !== $this->name ) return;
        if( $this->form->process( $_POST, true ) ){
            $this->form->updateMeta( $post_id );
        }
        else{
            foreach( $this->form->getFieldNames() as $name ){
                if( !$this->form->field($name)->hasError() ){
                    update_post_meta( $post_id, $name, $this->form->field($name)->getValue() );
                }
            }
        }
    }
    
    /**
     * @wp.meta_box
     * @wp.title                Entry Information
     */
    public function meta_box_entry( $post )
    {
        if( $post->post_type != $this->name ) return;
        $this->form->render(array(
            'renderer'          => 'Snap_Wordpress_Form_Renderer_AdminTable',
            'formerrors'        => true
        ));
    }
    
    public function get_counts( $start = null, $end = null, $campaign = null )
    {
        global $wpdb;
        $where = array("m.meta_key = 'campaign'", "e.post_status = 'publish'");
        if( $campaign ) $where[] = "m.meta_value = '$campaign'";
        if( $start ){
            $start = strtotime( $start );
            $where[] = "e.post_date >= '".date('Y-m-d H:i:s', $start)."'";
        }
        if( $end ){
            $end = strtotime( $end );
            $where[] = "e.post_date <= '".date('Y-m-d H:i:s', $end)."'";
        }
        
        
        $format = '%Y-%m-%d';
        if( $start && $end ){
            // get the diff
            $diff = $end - $start;
            if( $diff < 1000*60*60*24*20 ){
                $format = '%Y-%m-%d-%H';
            }
        }
        
        $where = implode(' AND ', $where);
        $query = "
            SELECT DATE_FORMAT(e.post_date, '$format') date,
                m.meta_value campaign,
                COUNT(*) entries
            FROM $wpdb->posts e
            LEFT JOIN $wpdb->postmeta m ON m.post_id = e.ID
            WHERE $where
            GROUP BY DATE_FORMAT(e.post_date, '$format')
            ";
        $counts = $wpdb->get_results($query);
        return $counts;
    }
    
}