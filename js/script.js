jQuery( document ).ready( function( $ ) {
    $.each(php_vars.popups, function(propName, propVal) {
        if(Cookies.get("popup-"+ propVal.popupId) != "yes"){  
            var popupDelay = Math.round(parseInt(propVal.popupDelay * 1000)),
                popupExpire = parseInt(propVal.popupExpire),
                triggerType = propVal.triggerType,
                triggerSection = propVal.triggerSection,
                impressions = propVal.impressions,
                adminUrl = propVal.ajaxurl,
                statsNonce = propVal.ajax_nonce,
                popupId = propVal.popupId;

            if(impressions == ""){
                impressions = 0;
            }

            console.log(impressions);
            if(triggerType == "section"){
                $(window).scroll(function() {
                    var targetOffset = $(triggerSection).offset().top,
                        wS = $(this).scrollTop();
                       
                    if (wS > targetOffset){
                        //console.log(popupId);  
                        $('#popup-' + popupId).fadeIn(350, function() {
                            Cookies.set("popup-" + popupId , "yes", {expires: popupExpire, path: '/'});
                            jQuery.ajax({ // We use jQuery instead $ sign, because Wordpress convention.
                                url : adminUrl, // This addres will redirect the query to the functions.php file, where we coded the function that we need.
                                type : 'POST',
                                data : {
                                    action : 'pui_stats_action', 
                                    fieldvalue : impressions,
                                    security: statsNonce,
                                    postid : popupId
                                },
                                beforeSend: function() {
                                       //console.log('Updating Field');
                                },
                                success : function( response ) {
                                     //console.log('Success');
                                },
                                complete: function(){
                                    //alert( "Field updated");
                                }
                            });
                        });
                        $(window).off('scroll');
                   }
                });
            }
            else{
                $('#popup-' + popupId).delay( popupDelay ).fadeIn(350, function() {
                    impressions = parseInt(impressions) + 1;
                    Cookies.set("popup-" + popupId , "yes", {expires: popupExpire, path: '/'});
                    jQuery.ajax({ // We use jQuery instead $ sign, because Wordpress convention.
                        url : adminUrl, // This addres will redirect the query to the functions.php file, where we coded the function that we need.
                        type : 'POST',
                        data : {
                            action : 'pui_stats_action', 
                            fieldvalue : impressions,
                            security: statsNonce,
                            postid : popupId
                        },
                        beforeSend: function() {
                               //console.log('Updating Field');
                        },
                        success : function( response ) {
                             //console.log('Success');
                        },
                        complete: function(){
                            //alert( "Field updated");
                        }
                    });
                });
            }
        }
       
    });
    //----- CLOSE
    $('[data-popup-close]').on('click', function(e)  {
        var targeted_popup = $(this).attr('data-popup-close');
        $('#' + targeted_popup).fadeOut(350);
		  e.preventDefault();
    });
});