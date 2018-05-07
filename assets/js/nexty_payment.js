function validURL(str) {
  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
  '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
  '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
  '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
  '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
  '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
  return pattern.test(str);
}


jQuery(function($) {
	$(".notice").hide(); //Hide Admin Notice
	
	$(".valid_url").on( 'change', function( event ){
		var address = this.value;
		if (!validURL(address)) {
			$(".notice").show();
		} 
		else $(".notice").hide();
	});
	
	jQuery("form.woocommerce-checkout").on('submit', function(event) {
        //alert("submiting");
		//event.preventDefault();
    });
});
