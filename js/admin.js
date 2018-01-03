jQuery( document ).ready( function( $ ) {
    console.log("test");



$("#trigger_type").change(function() {
	console.log("change");
    if($("#trigger_type").val()==="specific"){ 
    	console.log("selected");
    	$('#triggers_container').show();
    }
    else{
    	$('#triggers_container').hide();
    }
    if($("#trigger_type").val()==="section"){ 
    	console.log("selected");
    	$('#trigger_section_container').show();
    }
    else{
    	$('#trigger_section_container').hide();
    }
   
});
});