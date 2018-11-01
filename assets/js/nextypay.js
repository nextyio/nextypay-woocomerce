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

function isNumber(str) {
  if (!str) return false;
  regexp = /^[0-9]+$/;
  return regexp.test(str);
}

jQuery(function($) {
  function set_save_button(){
    jQuery('button[name=save]').prop("disabled",true);
    if (valid_walletAddress && valid_exchangeAPI ) jQuery('button[name=save]').prop("disabled",false);
  }

	$(".notice").hide(); //Hide Admin Notice

	var valid_exchangeAPI=true;
  var valid_walletAddress=true;

	$("#woocommerce_nextypay_exchangeAPI").on( 'keyup', function( event ){
		valid_exchangeAPI=validURL(this.value);
		if (valid_exchangeAPI) {$(".exchangeAPI").hide();} else $(".exchangeAPI").show();
    set_save_button();
	});

  $("#woocommerce_nextypay_walletAddress").on( 'keyup', function( event ){
    valid_walletAddress=isHex(this.value);
    if (valid_walletAddress) {$(".walletAddress").hide();} else $(".walletAddress").show();
    set_save_button();
  });

})
