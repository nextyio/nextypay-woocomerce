function validURL(str) {
  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
  '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
  '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
  '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
  '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
  '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
  return pattern.test(str);
}

function countInSecond(startTime,endTime) {
  var timeDiff = endTime - startTime; //in ms
  // strip the ms
  timeDiff /= 1000;

  // get seconds 
  var seconds = Math.round(timeDiff);
  return seconds;
}

//check paid sum and order status with ajax, in PHP ajax_action()

function call_ajax(startTime,order_total,order_id,timeout,interval){
	var seconds=countInSecond(startTime,new Date());
	console.log(seconds);
	if (seconds>timeout) {
		console.log("time out");
		return;
	}
	var paid="0";
	setTimeout(function(){
		// This does the ajax request
		jQuery.ajax({
			url : my_ajax_object.ajaxurl,
			type: 'POST',
		data : {
			'action' : 'my_action',
			'order_id' : order_id,
			'order_total': order_total,
		}
		}).done(function ( response ) {
			console.log(response);
			//alert (response);
			paid=response;
			if (paid[0]=="0") { return call_ajax(startTime,order_total,order_id,timeout,interval); } else 
			{
				console.log(response);
				//alert(response);
				var current_page= window.location.href;
				window.location = current_page;
				//alert (test);
				return;
			}
		}).fail(function ( err ) {

		})
	}, interval*1000);
}

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
