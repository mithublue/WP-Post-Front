<?php
/*
Plugin Name: WP Post Front
Plugin URI:
Description:
Version: 1.0.0
Author: Mithu A Quayium
Author URI:
License: GPL2
*/

/**
 * Copyright (c) YEAR Mithu A Quayium (email: cemithu06@gmail.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */


// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Post_Front
 *
 * Base class of the plugin
 */

class WP_Post_Front {

    public $post_tax = array();

    /**
     * Constructor for the WP_Post_Front class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {

        add_filter( 'the_content', array( $this, 'add_front_page_buttons'), 10 );
        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts_styles') );

        add_action( 'wp_ajax_front-post-action', array( $this, 'front_post_actions' ) );
        add_action( 'wp_ajax_nopriv_front-post-action', array( $this, 'front_post_actions' ) );

        add_action( 'wp_ajax_front-post-add-term', array( $this, 'front_term_add' ) );
        add_action( 'wp_ajax_nopriv_front-post-add-term', array( $this, 'front_term_add' ) );

        add_action( 'wp_ajax_wpf_save_post', array( $this, 'wpf_save_post' ) );
        add_action( 'wp_ajax_nopriv_wpf_save_post', array( $this, 'wpf_save_post' ) );

        $this->includes();
    }


    /**
     * Add button to the frontend post
     */
    public function add_front_page_buttons( $content ) {

        global $post;

        if( !is_single() && !is_page() ) return $content;

        if ( !is_user_logged_in() ) return $content;

        if ( !current_user_can( 'edit_posts' ) ) return $content;

        $buttongs = '<div id="front-post-actions"><a href="'.admin_url().'post-new.php?post_type='. get_post_type($post->ID).'" data-post_type="' . get_post_type($post->ID) . '" data-action="post-new" data-id="'.$post->ID.'" >' . __( 'Add New Post', 'wpf' ) . '</a></div>';
        return $buttongs.$content;

    }

    /**
     * Include necessary files
     */
    function includes(){
        require_once dirname(__FILE__).'/includes/wpf-functions.php';
    }

    /**
     * Initializes the WP_Post_Front() class
     *
     * Checks for an existing WP_Post_Front() instance
     * and if it doesn't find one, creates it.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function init() {

        static $instance = false;

        if ( ! $instance ) {
            $instance = new WP_Post_Front();
        }

        return $instance;
    }

    /**
     * Enqueuing scripts and styles to the frontend
     *
     * @return void
     */
    function wp_enqueue_scripts_styles() {
        global $post;

        if ( ( !is_single() && !is_page() )  || !is_user_logged_in() ) return;

        wp_enqueue_style( 'wpf-style', plugins_url( 'assets/style.css', __FILE__ ) );
        wp_enqueue_script( 'wpf-script', plugins_url( 'assets/scripts.js', __FILE__ ), array( 'jquery', 'jquery-ui-autocomplete' ) );
        wp_localize_script( 'wpf-script' , 'wpf_data' , array(
            'main_nonce' => wp_create_nonce( 'wpf-create_post' ),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'post_type' => get_post_type($post->ID)
        ) );

        wp_enqueue_media();
    }


    /**
     * ajax to pop up the form form post
     */
    function front_post_actions() {

        global $post;

        if ( !is_user_logged_in() ) return;

        if ( !current_user_can( 'edit_posts' ) ) return;

        include_once dirname( __FILE__ ) . '/includes/post-new-template.php';

        exit;
    }




    /**
     * Add/create new term to a taxonomy
     */
    function front_term_add() {
        if( !is_user_logged_in() ) return;

        if( !current_user_can( 'manage_options' ) ) return;

        if( !taxonomy_exists( $_POST['taxonomy'] ) ) return;

        $term = filter_var ( $_POST['term'], FILTER_SANITIZE_STRING );
        $responce = wp_insert_term( $term, $_POST['taxonomy'] );
        echo json_encode($responce);
        exit;
    }

    /**
     * Save post
     */
    function wpf_save_post() {

        if( !wp_verify_nonce( $_POST['token'], 'wpf-create_post' ) ) return;

        if( !post_type_exists( $_POST['post_type'] ) ) return;

        if( !current_user_can( 'edit_posts') ) return;

        parse_str($_POST['postdata'],$postdata);

        $postdata['post_type'] = $_POST['post_type'];

        $post_id = wp_insert_post($postdata);

        if( $post_id ) {
            $res = array(
                'id' => $post_id,
                'redirect_url' => get_permalink($post_id)
            );
        } else {
            $res = array(
                'error' => $_POST['post_type'].' not created !'
            );
        }
        echo json_encode($res);
        exit;
    }
}

WP_Post_Front::init();