function countInSecond(startTime,endTime) {
  var timeDiff = endTime - startTime; //in ms
  // strip the ms
  timeDiff /= 1000;

  // get seconds
  var seconds = Math.round(timeDiff);
  return seconds;
}

function call_ajax(startTime,order_id,timeout,interval){

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
      url: nextypay_ajax_obj.ajaxurl,//ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
      data: {
          'action': 'ntyp_updatedb_ajax'
      },
		}).done(function ( response ) {

		}).fail(function ( err ) {
      console.log(err);
		})
    ////////////////////////////
    jQuery.ajax({
        url: nextypay_ajax_obj.ajaxurl,//ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
        data: {
            'action': 'ntyp_get_order_status_ajax',
            'order_id' : order_id
        },
        success:function(response) {
            // This outputs the result of the ajax request
            console.log("Loading Blocks");
            //console.log(response);
            //alert (response);
            paid=response;
            if (paid[0]=="1") {
              console.log("Done!");
              var current_page= window.location.href;
              window.location = current_page;
              return;
            } else
            {
              call_ajax(startTime,order_id,timeout,interval);
            }
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
    ///////////////////////////
	}, interval*1000);
}
