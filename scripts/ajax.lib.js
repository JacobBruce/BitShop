  function ajax_error(jqXHR, textStatus, errorThrown) {
    console.log(jqXHR.responseText);
	if (jqXHR.status === 0) {
		return 'AJAX ERROR: Cannot connect to the server';
	} else if (jqXHR.status == 404) {
		return 'AJAX ERROR: Page not found [404]';
	} else if (jqXHR.status == 500) {
		return 'AJAX ERROR: Internal server error [500]';
	} else if (textStatus === 'parsererror') {
		return 'AJAX ERROR: Failed to parse JSON';
	} else if (textStatus === 'timeout') {
		return 'AJAX ERROR: The request timed out';
	} else if (textStatus === 'abort') {
		return 'AJAX ERROR: The request was aborted';
	} else {
		return 'AJAX ERROR: '+errorThrown;
	}
  }

  function ajax_call(call_type, targ_url, call_data, succ_func, error_func) {
	if ($.support.ajax != false) {
		$.ajax({
			type: call_type,
			url: targ_url,
			data: call_data,
			cache: false,
			success: function(response) {
				succ_func(response);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				error_func(ajax_error(jqXHR, textStatus, errorThrown));
			}
		});
	} else {
	  error_func('AJAX ERROR: web browser does not support ajax');
	}
  }
  
  function ajax_post(targ_url, post_data, succ_func, error_func) {
    ajax_call('POST', targ_url, post_data, succ_func, error_func);
  }

  function ajax_get(targ_url, get_data, succ_func, error_func) {
    ajax_call('GET', targ_url, get_data, succ_func, error_func);
  }