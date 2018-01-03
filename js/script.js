jQuery( document ).ready( function( $ ) {
     //----- OPEN


    $.each(php_vars.popups, function(propName, propVal) {
        //console.log(this.id);
        if(Cookies.get("popup-popup-"+ this.id) != "yes"){  
            var popupDelay = Math.round(parseInt(this.popupDelay * 1000));
            var popupExpire = parseInt(this.popupExpire);
            var triggerType = this.triggerType;
            var triggerSection = this.triggerSection;

            if(triggerType == "section"){
              
            }
            else{
                $('#popup-' + this.id).delay( popupDelay ).fadeIn(350, function() {
                    Cookies.set("popup-" + this.id , "yes", {expires: popupExpire, path: '/'});
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