<?php

function wpf_post_format_meta_box( $post_id , $box ) {
    if ( current_theme_supports( 'post-formats' ) && post_type_supports( get_post_type( $post_id ), 'post-formats' ) ) :
        $post_formats = get_theme_support( 'post-formats' );

        if ( is_array( $post_formats[0] ) ) :
            $post_format = get_post_format( $post_id );
            if ( !$post_format )
                $post_format = '0';
            // Add in the current one if it isn't there yet, in case the current theme doesn't support it
            if ( $post_format && !in_array( $post_format, $post_formats[0] ) )
                $post_formats[0][] = $post_format;
            ?>
            <div id="post-formats-select">
                <fieldset>
                    <legend class="screen-reader-text"><?php _e( 'Post Formats' ); ?></legend>
                    <input type="radio" name="post_format" class="post-format" id="post-format-0" value="0" <?php checked( $post_format, '0' ); ?> /> <label for="post-format-0" class="post-format-icon post-format-standard"><?php echo get_post_format_string( 'standard' ); ?></label>
                    <?php foreach ( $post_formats[0] as $format ) : ?>
                        <br /><input type="radio" name="post_format" class="post-format" id="post-format-<?php echo esc_attr( $format ); ?>" value="<?php echo esc_attr( $format ); ?>" <?php checked( $post_format, $format ); ?> /> <label for="post-format-<?php echo esc_attr( $format ); ?>" class="post-format-icon post-format-<?php echo esc_attr( $format ); ?>"><?php echo esc_html( get_post_format_string( $format ) ); ?></label>
                    <?php endforeach; ?>
                </fieldset>
            </div>
        <?php endif; endif;
}


/**
 * Non heirerichical taxonomy to render
 * @param $post
 * @param $box
 */
function wpf_post_tags_meta_box( $post_id, $box ) {
    $defaults = array( 'taxonomy' => 'post_tag' );
    if ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) ) {
        $args = array();
    } else {
        $args = $box['args'];
    }
    $r = wp_parse_args( $args, $defaults );
    $tax_name = esc_attr( $r['taxonomy'] );
    $taxonomy = get_taxonomy( $r['taxonomy'] );
    $user_can_assign_terms = current_user_can( $taxonomy->cap->assign_terms );
    $comma = _x( ',', 'tag delimiter' );
    $terms_to_edit = get_terms_to_edit( $post_id, $tax_name );
    if ( ! is_string( $terms_to_edit ) ) {
        $terms_to_edit = '';
    }
    ?>
    <div class="tagsdiv" id="<?php echo $tax_name; ?>">
        <div class="jaxtag">
            <div class="nojs-tags hide-if-js">
                <label for="tax-input-<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_or_remove_items; ?></label>
                <p><textarea name="<?php echo "tax_input[$tax_name]"; ?>" rows="3" cols="20" class="the-tags" id="tax-input-<?php echo $tax_name; ?>" <?php disabled( ! $user_can_assign_terms ); ?> aria-describedby="new-tag-<?php echo $tax_name; ?>-desc"><?php echo str_replace( ',', $comma . ' ', $terms_to_edit ); // textarea_escaped by esc_attr() ?></textarea></p>
            </div>
            <?php if ( $user_can_assign_terms ) : ?>
                <div class="ajaxtag hide-if-no-js">
                    <label class="screen-reader-text" for="new-tag-<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_new_item; ?></label>
                    <p><input type="text" id="new-tag-<?php echo $tax_name; ?>" name="newtag[<?php echo $tax_name; ?>]" class="newtag form-input-tip" size="16" autocomplete="off" aria-describedby="new-tag-<?php echo $tax_name; ?>-desc" value="" />
                        <input type="button" class="button tagadd" data-tax="<?php echo $tax_name ?>" value="<?php esc_attr_e('Add'); ?>" /></p>
                </div>
                <p class="howto" id="new-tag-<?php echo $tax_name; ?>-desc"><?php echo $taxonomy->labels->separate_items_with_commas; ?></p>
            <?php endif; ?>
        </div>
        <div class="tagchecklist"></div>
    </div>
    <?php if ( $user_can_assign_terms ) : ?>
        <p class="hide-if-no-js"><a href="#titlediv" class="tagcloud-link" id="link-<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->choose_from_most_used; ?></a></p>
    <?php endif; ?>
<?php
}


function wpf_post_categories_meta_box( $post_id, $box ) {
    $defaults = array( 'taxonomy' => 'category' );
    if ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) ) {
        $args = array();
    } else {
        $args = $box['args'];
    }
    $r = wp_parse_args( $args, $defaults );
    $tax_name = esc_attr( $r['taxonomy'] );
    $taxonomy = get_taxonomy( $r['taxonomy'] );
    ?>
    <div id="taxonomy-<?php echo $tax_name; ?>" class="categorydiv wpf-meta-box">
        <div><?php echo $tax_name; ?></div>
        <div id="<?php echo $tax_name; ?>-pop" class="tabs-panel" style="display: none;">
            <ul id="<?php echo $tax_name; ?>checklist-pop" class="categorychecklist form-no-clear" >
                <?php $popular_ids = wp_popular_terms_checklist( $tax_name ); ?>
            </ul>
        </div>

        <div id="<?php echo $tax_name; ?>-all" class="tabs-panel">
            <?php
            $name = ( $tax_name == 'category' ) ? 'post_category' : 'tax_input[' . $tax_name . ']';
            echo "<input type='hidden' name='{$name}[]' value='0' />"; // Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
            ?>
            <ul id="<?php echo $tax_name; ?>checklist" data-wp-lists="list:<?php echo $tax_name; ?>" class="categorychecklist form-no-clear">
                <?php wp_terms_checklist( $post_id, array( 'taxonomy' => $tax_name, 'popular_cats' => $popular_ids ) ); ?>
            </ul>
        </div>
        <?php if ( current_user_can( $taxonomy->cap->edit_terms ) ) : ?>
            <div id="<?php echo $tax_name; ?>-adder" class="wp-hidden-children">
                <a id="<?php echo $tax_name; ?>-add-toggle" href="#<?php echo $tax_name; ?>-add" class="hide-if-no-js taxonomy-add-new">
                    <?php
                    /* translators: %s: add new taxonomy label */
                    printf( __( '+ %s' ), $taxonomy->labels->add_new_item );
                    ?>
                </a>
                <p id="<?php echo $tax_name; ?>-add" class="category-add wp-hidden-child">
                    <label class="screen-reader-text" for="new<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_new_item; ?></label>
                    <input type="text" name="new<?php echo $tax_name; ?>" id="new<?php echo $tax_name; ?>" class="form-required form-input-tip new-tax-input" value="<?php echo esc_attr( $taxonomy->labels->new_item_name ); ?>" aria-required="true"/>
                    <label class="screen-reader-text" for="new<?php echo $tax_name; ?>_parent">
                        <?php echo $taxonomy->labels->parent_item_colon; ?>
                    </label>
                    <input type="button" data-tax="<?php echo $tax_name; ?>" id="<?php echo $tax_name; ?>-add-submit" data-wp-lists="add:<?php echo $tax_name; ?>checklist:<?php echo $tax_name; ?>-add" class="button category-add-submit" value="<?php echo esc_attr( $taxonomy->labels->add_new_item ); ?>" />
                    <?php wp_nonce_field( 'add-' . $tax_name, '_ajax_nonce-add-' . $tax_name, false ); ?>
                    <span id="<?php echo $tax_name; ?>-ajax-response"></span>
                </p>
            </div>
        <?php endif; ?>
    </div>
<?php
}