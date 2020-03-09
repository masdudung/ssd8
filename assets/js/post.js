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

    } ) 
} )

