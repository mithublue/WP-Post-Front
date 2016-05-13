<style>
    .front-post-modal {
        background: #eeeeee;
        position: fixed;
        z-index: 999;
        left: 0;
        right: 0;
        top: 50px;
        bottom: 0;
        overflow: scroll;
    }
</style>
<?php

if( !isset( $_POST['data_action'] ) || !isset( $_POST['id']) || !isset( $_POST['post_type'] ) ) return;

if( !empty( $_POST['id']) && !is_numeric( $_POST['id'] ) ) return;

$post_type = $_POST['post_type'];
$post_id = $_POST['id'];

if ( $_POST['data_action'] == 'post-new' ) :

    echo '<div id="wpf-add-post-container"><form class="wpf-add-post-form">';

    if( post_type_supports( $post_type, 'title' ) )  :
        ?>
        <div class="wpf-form-field">
            <label for="post_title">Title</label>
            <input name="post_title" size="30" value="" id="title" spellcheck="true" autocomplete="off" type="text">
        </div>
        <?php
    endif;

    if( post_type_supports( $post_type, 'editor' ) )  :
        ?>
        <div class="wpf-form-field">
            <label for="content">Content</label>
            <?php wp_editor( '', 'post_content' );?>
        </div>
        <?php
    endif;

    if( post_type_supports( $post_type, 'thumbnail' ) )  :
        ?>

        <?php
    endif;

    if( post_type_supports( $post_type, 'excerpt' ) )  :
        ?>
        <div class="wpf-form-field">
            <label for="excerpt">Excerpt</label>
            <?php wp_editor( '', 'post_excerpt' );?>
        </div>
    <?php
    endif;

    if( post_type_supports( $post_type, 'comments' ) )  :
        ?>
        <div class="wpf-form-field">
            <label for="wpf-allow-post-comments">Allow Comments</label>
            <select name="comment_status" id="">
                <option value="open">Yes</option>
                <option value="closed">No</option>
            </select>
        </div>
        <?php
    endif;

    if( post_type_supports( $post_type, 'post-formats' ) )  :

        if ( current_theme_supports( 'post-formats' ) ) :
            $post_formats = get_theme_support( 'post-formats' );

            if ( is_array( $post_formats[0] ) ) {

                $formats = $post_formats[0];
                ?>
                <div class="wpf-form-field">
                    <label for="wpf-post-format">Format</label>
                    <select name="wpf-post-format">';
                    <?php
                    foreach( $formats as $key => $format ) :
                        ?>
                        <option value="<?php echo $format;?>"><?php _e( ucfirst($format), 'wpf' ); ?></option>
                    <?php
                    endforeach;
                    ?>
                    </select>
                </div>
            <?php
            }

        endif;
    endif;

    //taxonomy meta box
    $taxonomies = get_object_taxonomies( $post_type, 'object' );

    foreach( $taxonomies as $tax_name => $tax_array ) {

        if ( $tax_name == 'post_format' ) {

            wpf_post_format_meta_box( $post_id, array(

                'args' => array(
                    'taxonomy' => $tax_name
                )

            ) );
        }  else {

            if( $tax_array->hierarchical ) {

                wpf_post_categories_meta_box( $post_id, array(
                    'args' => array(
                        'taxonomy' => $tax_name
                    )
                ) );
            } else {

                wpf_post_tags_meta_box( $post_id, array(
                    'args' => array(
                        'taxonomy' => $tax_name
                    )
                ) );
            }


        }
    }
    ?>
    <select name="post_status">
        <?php
        $post_statuses = get_post_statuses();
        foreach( $post_statuses as $status => $value ) :
            echo '<option value="'.$status.'">'.$value.'</option>';
        endforeach;
        ?>
    </select>
    <input type="submit" id="post-cancel" value="<?php _e( 'Cancel', 'wpf'); ?>">
    <input type="submit" id="post-submit" value="<?php _e( 'Add Post', 'wpf'); ?>">
    <?php
    echo '</form></div>';

endif;