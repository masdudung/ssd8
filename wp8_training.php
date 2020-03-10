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
        $url = get_rest_url() . 'wp/v2/posts?' . http_build_query( $args );

        $response = wp_remote_get( $url ); 
        return wp_remote_retrieve_body($response);
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
        add_submenu_page(
            'API-post.php',
            'API post add',
            'API post add',
            'manage_options',
            'API-post-add.php',
            [$this, 'API_post_add']
        );
    }
    function API_post_add()
    {
        $wp_request_headers = array(
            'Authorization' => 'Basic ' . base64_encode( 'admin:admin123' )
        );
          
        $url = get_rest_url() . 'wp/v2/posts/60';
        $wp_delete_post_response = wp_remote_request(
            $url,
            array(
                'method'    => 'DELETE',
                'headers'   => $wp_request_headers
            )
        );
        echo wp_remote_retrieve_response_code( $wp_delete_post_response ) . ' ' . wp_remote_retrieve_response_message( $wp_delete_post_response );

        // echo admin_url('admin.php?page=API-post.php&tab=1');
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

    function API_post_form()
    {

        echo "<h1>List Post</h1>";
        echo "<br>";
        // The Query
        $the_query = new WP_Query( 
            array(
                'post_type' => 'post',
                'orderby'   => 'id',
                'order'     => 'DESC'
            ) 
        );
        
        echo "<div class='md-post-list'><table>";
        // The Loop
        if ( $the_query->have_posts() ) {

            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                
                echo "<tr>";
                echo '<td>'.get_the_title().'</td>';
                echo '<td>
                        <button class="md-post-edit" id="'. get_the_ID() .'">Edit</button>
                        <button class="md-post-delete" id="'.get_the_ID().'">Delete</button>
                    </td>';
                echo '</tr>';
            }
            
        } else {
            echo 'no posts found';
        }
        /* Restore original Post Data */
        wp_reset_postdata();
        echo "</table></div>";
        
        
        echo '<div class="md-post-edit-form">';
        echo '<form><table class="form-table" role="presentation">
                <tbody>
                    <tr class="form-field form-required">
                        <th scope="row"><label for="md-post-title">
                            Title <span class="description">(required)</span></label>
                        </th>
                        <td>
                            <input type="hidden" name="md-post-id" value="" maxlength="60">
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
                            <button type="button" id="md-post-cancel">Cancel</button>
                            <button type="button" id="md-post-update">Update</button>
                        </td>
                    </tr>
                </tbody>
            </table>';
        echo '</div>';
        ?>
        <?php

    }

    function wp_api() {

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'wp-api' );
        wp_enqueue_script( 'my_script', plugin_dir_url( __FILE__ ).'/assets/js/post.js', array( 'wp-api' ) );
        wp_enqueue_style( 'md-style', plugin_dir_url( __FILE__ ).'/assets/css/md-style.css', false, '1.0.0', 'all');

        wp_localize_script( 'wp-api', 'wpApiSettings', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' )
        ) );
    }

}

$plugin = new wp8_training();

