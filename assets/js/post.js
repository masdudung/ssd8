var $ = jQuery.noConflict();

$('document').ready( function() {
    
    // button save click
    $('#md-post-save').click( function(){
        
        var title = $('input[name="md-post-title"]').val()
        var content = $('input[name="md-post-content"]').val()

        if(!title || !content)
        {
            alert('you should fill textbox below')
            return false
        }

        wp.api.loadPromise.done(function() {
            
            var post = new wp.api.models.Post({
                status: 'publish',
                title: title,
                content: content,
            });
        
            post.save(null, {
                success: function(model, response, options) 
                {
                    alert('Post Saved')
                    $('input[name="md-post-title"]').val('')
                    $('input[name="md-post-content"]').val('')
                    
                    $('#md-post-link').html("<a href='"+ response.link +"' target='_blank'>"+ response.link +"</a>");
                },
                error: function(model, response, options) {
                }
            });
        
        }) // end wp.api.loadPromise

    } ) // end button save click

    // button save click
    $('.md-post-edit').click( function(){

        var md_post_id = $(this).attr('id') 
        var post = new wp.api.models.Post( { id: md_post_id } );
        post.fetch().done(function (data, status) {
            if(status='success')
            {
                console.log(data)
                $('.md-post-list').hide()
                $('.md-post-edit-form').css('display','block')

                $('input[name="md-post-id"]').val( data.id )
                $('input[name="md-post-title"]').val( data.title.rendered )
                $('input[name="md-post-content"]').val( data.content.rendered )
            }

        }).fail(function () 
        {
            alert('no post found')
        })

    } ) // end button save click

    $('#md-post-update').click(function () {

        var id = $('input[name="md-post-id"]').val()
        var title = $('input[name="md-post-title"]').val()
        var content = $('input[name="md-post-content"]').val()
        
        $.ajax( {
            url: wpApiSettings.root + 'wp/v2/posts/'+ id,
            method: 'POST',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
            },
            data:{
                'title' : title,
                'content' : content
            }
        } ).done( function ( response, status ) {
            if(status=='success')
            {
                alert('Post Updated')
                location.reload()
            }
        } )
    })

    $('.md-post-delete').click(function () {

        var id = $(this).attr('id')
        console.log(id)
        
        $.ajax( {
            url: wpApiSettings.root + 'wp/v2/posts/'+ id,
            method: 'DELETE',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
            },
        } ).done( function ( response, status ) {
            if(status=='success')
            {
                alert('Post deleted')
                location.reload()
            }
        } )

    })

    $('#md-post-cancel').click(function(){
        $('input[name="md-post-id"]').val('')
        $('input[name="md-post-title"]').val('')
        $('input[name="md-post-content"]').val('')

        $('.md-post-list').show()
        $('.md-post-edit-form').hide()
    })
} )

