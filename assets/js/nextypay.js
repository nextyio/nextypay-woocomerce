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

/*
function set_save_button(){
  jQuery('button[name=save]').prop("disabled",true);
  if (valid_endPointAddress && valid_walletAddress && valid_exchangeAPI && valid_blocks_min && valid_blocks_max && valid_blocks_load ) jQuery('button[name=save]').prop("disabled",false);
}
*/
//check paid sum and order status with ajax, in PHP ajax_action()

jQuery(function($) {
	//alert("test");
  function set_save_button(){
    var min=parseInt($("#woocommerce_nextypay_min_blocks_saved_db").val());
    var max=parseInt($("#woocommerce_nextypay_max_blocks_saved_db").val());
    jQuery('button[name=save]').prop("disabled",true);
    if (valid_endPointAddress && valid_walletAddress && valid_exchangeAPI && valid_blocks_min && valid_blocks_max && valid_blocks_load && (min<max) ) jQuery('button[name=save]').prop("disabled",false);
  }

	$(".notice").hide(); //Hide Admin Notice

	var valid_endPointAddress=true;
	var valid_exchangeAPI=true;
  var valid_walletAddress=true;
  var valid_blocks_max=true;
  var valid_blocks_min=true;
  var valid_blocks_load=true;

  //if (valid_endPointAddress) {$(".endPoint").hide();} else $(".endPoint").show();
  //if (valid_exchangeAPI) {$(".exchangeAPI").hide();} else $(".exchangeAPI").show();
  //if (valid_walletAddress) {$(".walletAddress").hide();} else $(".walletAddress").show();

	$("#woocommerce_nextypay_endPointAddress").on( 'keyup', function( event ){
		valid_endPointAddress=validURL(this.value);
		if (valid_endPointAddress) {$(".endPoint").hide();} else $(".endPoint").show();
    set_save_button();
	});

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

  $("#woocommerce_nextypay_min_blocks_saved_db").on( 'keyup', function( event ){
    var min=parseInt(this.value);
    var max=parseInt($("#woocommerce_nextypay_max_blocks_saved_db").val());
    console.log(min+""+max);
    valid_blocks_min=isNumber(this.value);
    if (valid_blocks_min) {
      $(".blocks_min").hide();
      if (min>max) {$(".compare_min_max").show();} else {$(".compare_min_max").hide();};
    } else $(".blocks_min").show();
    set_save_button();
  });

  $("#woocommerce_nextypay_max_blocks_saved_db").on( 'keyup', function( event ){
    var max=parseInt(this.value);
    var min=parseInt($("#woocommerce_nextypay_min_blocks_saved_db").val());
    console.log(min+""+max);
    valid_blocks_max=isNumber(this.value);
    if (valid_blocks_max) {
      $(".blocks_max").hide();
      if (min>max) {$(".compare_min_max").show();} else {$(".compare_min_max").hide();};
    } else $(".blocks_max").show();

    set_save_button();
  });

  $("#woocommerce_nextypay_blocks_loaded_each_request").on( 'keyup', function( event ){
    valid_blocks_load=isNumber(this.value);
    if (valid_blocks_load) {$(".blocks_load").hide();} else $(".blocks_load").show();
    set_save_button();
  });

})
