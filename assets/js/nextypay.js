function validURL(str) {
  if (!str) return false;
  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
  '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
  '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
  '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
  '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
  '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
  return pattern.test(str);
}

function isHex(str) {
  if (!str) return false;
  var subStr= str.toUpperCase();
  if (subStr.substr(0,2) === '0X') subStr=subStr.substr(2);
  regexp = /^[0-9a-fA-F]+$/;
  return regexp.test(subStr);
}

//check paid sum and order status with ajax, in PHP ajax_action()

jQuery(function($) {
	//alert("test");

	$(".notice").hide(); //Hide Admin Notice

	var valid_endPointAddress=true;
	var valid_exchangeAPI=true;
  var valid_walletAddress=true;

  //if (valid_endPointAddress) {$(".endPoint").hide();} else $(".endPoint").show();
  //if (valid_exchangeAPI) {$(".exchangeAPI").hide();} else $(".exchangeAPI").show();
  //if (valid_walletAddress) {$(".walletAddress").hide();} else $(".walletAddress").show();

	$("#woocommerce_nextypay_endPointAddress").on( 'keyup', function( event ){
		valid_endPointAddress=validURL(this.value);
		if (valid_endPointAddress) {$(".endPoint").hide();} else $(".endPoint").show();
    $('button[name=save]').prop("disabled",true);
    if (valid_endPointAddress && valid_walletAddress && valid_exchangeAPI) $('button[name=save]').prop("disabled",false);
	});

	$("#woocommerce_nextypay_exchangeAPI").on( 'keyup', function( event ){
		valid_exchangeAPI=validURL(this.value);
		if (valid_exchangeAPI) {$(".exchangeAPI").hide();} else $(".exchangeAPI").show();
    $('button[name=save]').prop("disabled",true);
    if (valid_endPointAddress && valid_walletAddress && valid_exchangeAPI) $('button[name=save]').prop("disabled",false);
	});

  $("#woocommerce_nextypay_walletAddress").on( 'keyup', function( event ){
    valid_walletAddress=isHex(this.value);
    console.log(this.value + isHex(this.value));
    if (valid_walletAddress) {$(".walletAddress").hide();} else $(".walletAddress").show();
    $('button[name=save]').prop("disabled",true);
    if (valid_endPointAddress && valid_walletAddress && valid_exchangeAPI) $('button[name=save]').prop("disabled",false);
  });

})
