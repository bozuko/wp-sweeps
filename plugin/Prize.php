<?php

/**
 * @wp.posttype.name                    sweep_prize
 * @wp.posttype.single                  Prize
 * @wp.posstype.plural                  Prizes
 *
 * @wp.posttype.args.public             false
 * @wp.posttype.args.publicly_queryable false
 * @wp.posttype.args.show_ui            false
 * 
 */

class Sweeps_Prize extends Snap_Wordpress_PostType
{
    
    public function get_campaign_prizes( $campaign_id )
    {
        $q = new WP_Query(array(
            'posts_per_page'    => -1,
            'post_type'         => $this->name,
            'meta_query'        => array(
                array(
                    'key'               => 'campaign',
                    'value'             => $campaign_id
                )
            ),
            'meta_key'          => 'order',
            'orderby'           => 'meta_value_num',
            'order'             => 'ASC'
        ));
        foreach( $q->posts as &$post ){
            $form = new Sweeps_Prize_Form();
            $form->loadMeta( $post->ID );
            foreach( $form->getValues() as $key => $value ){
                $post->$key = $value;
            }
        }
        return $q;
    }
}