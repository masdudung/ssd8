<?php

/*
    Plugin Name: wp8_training
    Plugin URI: http://jadipesan.com/
    Description: wordpress api.
    Version: 1.0
    Author: Moch Mufiddin
    Author URI: http://jadipesan.com/
    License: GPLv2
*/

class wp8_training {

    private $posts_url = 'http://localhost/ssd6/index.php/wp-json/wp/v2/posts?';

    function __construct()
    {
        #register sortcode
        add_shortcode( 'latest_post', [$this, 'latest_post'] );

        #register admin menu
        add_action( 'admin_menu', [$this, 'API_post_menu'] );

        #register js in admin page
        add_action( 'admin_enqueue_scripts', [$this, 'wp_api'] );

    }

    # sortcode design
    function latest_post($atts)
    {
        $attributes = shortcode_atts( 
            array(
                'limit' => 1,
            ), $atts
        );

        $limit = (int) $attributes['limit'];
        $posts = $this->_get_latest_post( $limit );

        $posts = json_decode($posts);
        if($posts)
        {
            foreach($posts as $post){
                $this->show_post_template( $post );
            }
        }else{
            echo "no post found";
        }
    }

    function _get_latest_post( $limit )
    {
        $args = array(
            '_fields'   => 'author,id,title,excerpt,link',
            'per_page'  => $limit,
            'orderby'   => 'id',
            'order'     => 'desc'
        );
        $url = $this->posts_url . http_build_query( $args );

        $response = wp_remote_get( $url );
        if ( is_array( $response ) ) {
            $header = $response['headers']; 
            $body = $response['body']; 

            return $body;
        }
        return '[]'; 
    }

    private function show_post_template($post)
    {
        echo '<div class="blog-post">';
        echo '<h2 class="blog-post-title">';
        echo '<a href="'. $post->link .'">'. $post->title->rendered .'</a>';
        echo '</h2>';
        echo $post->excerpt->rendered;
        echo '</div>';
    }

    function API_post_menu() {
        add_menu_page( 'API post', 'API post', 'manage_options', 'API-post.php', [$this,'API_post_form'], 'dashicons-tickets', 6  );
    }

    function API_post_form()
    {   
        ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr class="form-field form-required">
                    <th scope="row"><label for="md-post-title">
                        Title <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" name="md-post-title" value="" maxlength="60">
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="md-post-title">
                        Content <span class="description"></span></label>
                    </th>
                    <td>
                        <input type="text" name="md-post-content" value="" maxlength="60">
                        <p id="md-post-link"></p>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="md-post-save">
                    </th>
                    <td>
                        <button type="button" id="md-post-save">Simpan</button>
                    </td>
                </tr>
            </tbody>
        </table>
    
        <?php
    }

    function wp_api() {

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'wp-api' );
        wp_enqueue_script( 'my_script', plugin_dir_url( __FILE__ ).'/assets/js/post.js', array( 'wp-api' ) );
    }

}

$plugin = new wp8_training();

