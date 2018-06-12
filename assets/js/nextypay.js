function validURL(str) {
  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
  '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
  '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
  '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
  '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
  '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
  return pattern.test(str);
}

//check paid sum and order status with ajax, in PHP ajax_action()

jQuery(function($) {
	//alert("test");

	$(".notice").hide(); //Hide Admin Notice

	var valid_endPointAddress=validURL($("#woocommerce_custom_endPointAddress").val());
	var valid_exchangeAPI=validURL($("#woocommerce_custom_exchangeAPI").val());

	if (valid_endPointAddress && valid_exchangeAPI) {$(".notice").hide();} else $(".notice").show();

	$("#woocommerce_custom_endPointAddress").on( 'change', function( event ){
		valid_endPointAddress=validURL(this.value);
		if (valid_endPointAddress && valid_exchangeAPI) {$(".notice").hide();} else $(".notice").show();
	});

	$("#woocommerce_custom_exchangeAPI").on( 'change', function( event ){
		valid_exchangeAPI=validURL(this.value);
		if (valid_endPointAddress && valid_exchangeAPI) {$(".notice").hide();} else $(".notice").show();
	});

})
