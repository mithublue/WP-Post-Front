;(function ($) {
    $(document).ready( function() {

        var wpf_script = {

            init : function() {

                $(document).on( 'click', '.wpf-meta-box .category-add-submit' , function() {
                    var term = $('#new' + $(this).data('tax')).val();
                    var taxonomy = $(this).data('tax');
                    wpf_script.addTaxonomy( term, taxonomy );
                });

                //tag
                $(document).on('click','.tagadd',function(){
                    wpf_script.addTag($(this).data('tax'));
                });

                $('#front-post-actions').on( 'click', 'a' , function() {

                    var data_action = $(this).data( 'action' );
                    var data_post_type = $(this).data('post_type');
                    var data_id = '';

                    if( data_action == 'edit' ) {
                        data_id = $(this).data('id');
                    }

                    wpf_script.create_post_form( data_action, data_id , data_post_type );

                    return false;

                } );

                $(document).on( 'click' , '#post-cancel' , function() {
                    $('.front-post-modal').remove(); return false;
                });

                $(document).on( 'click' , '#post-submit' , function() {
                    wpf_script.save_post();
                   // $('.front-post-modal').remove(); return false;
                    return false;
                });
            },

            create_post_form : function( data_action, data_id, data_post_type ) {

                $.post(
                    wpf_data.ajaxurl,
                    {
                        action : 'front-post-action',
                        data_action : data_action,
                        id : data_id,
                        post_type : data_post_type
                    },
                    function(data) {
                        $('body').append('<div class="front-post-modal">' + data + '</div>');
                    }
                );
            },

            addTaxonomy : function( term, taxonomy ) {

                $.post(
                    wpf_data.ajaxurl,
                    {
                        action : 'front-post-add-term',
                        taxonomy : taxonomy,
                        term : term
                    },
                    function(res) {
                        if( res ) {
                            var data = JSON.parse(res);
                            console.log(data);
                            if( typeof data == 'object' ) {
                                if ( !data.errors ) {
                                    wpf_script.addTaxData( term , taxonomy , data );
                                } else {
                                    for( e in data.errors ) {
                                        alert(data.errors[e]);
                                    }
                                }

                            }


                        }
                    }
                )
            },

            addTaxData : function( term, taxonomy ,data ) {
                $('#' + taxonomy + 'checklist').append(
                '<li id="'+ taxonomy +'-'+ data.term_id +'">' +
                '<label class="selectit">' +
                '<input value="'+ data.term_id + '" name="post_category[]" id="in-'+ taxonomy +'-'+ data.term_id +'" type="checkbox">' +
                ' ' + term +
                '</label>' +
                '</li>'
                );
                //
            },

            addTag : function( taxonomy,  tagAddBtn ) {
                $('#tax-input-' + taxonomy).val( $('input[name="newtag['+ taxonomy +']"]').val() );
                $('input[name="newtag['+ taxonomy +']"]').val('');
            },

            save_post : function() {

                    $.post(
                        wpf_data.ajaxurl,
                        {
                            action : 'wpf_save_post',
                            postdata : $('form.wpf-add-post-form').serialize()
                        },
                        function(res) {
                            console.log(res);
                        }
                    )
            }
        };

        wpf_script.init();

    } );


}(jQuery));