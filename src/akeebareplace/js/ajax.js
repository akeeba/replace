/*
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

// Object initialization
if (typeof(akeeba) === 'undefined')
{
	var akeeba = {};
}

/**
 * Callback for displaying AJAX error messages
 *
 * @callback akeeba_ajax_error_handler
 *
 * @param {string} message  The error message
 */

/**
 * Callback for processing AJAX return values
 *
 * @callback akeeba_ajax_message_handler
 *
 * @param {object} data  The JSON-decoded AJAX return value
 */

/**
 * AJAX connector object
 *
 * @param   {string}  ajaxEndpointURL  The AJAX endpoint URL
 */
function AkeebaReplaceAjax(ajaxEndpointURL)
{
	/**
	 * The AJAX endpoint URL to contact
	 *
	 * @type  {string}
	 */
	this.url = ajaxEndpointURL;

	/**
	 * Run an AJAX request
	 *
	 * @param {object} data The data to send. Use _akeeba_ajax_method to define POST (default) or GET.
	 * @param {akeeba_ajax_message_handler} successCallback Callback for successful AJAX requests
	 * @param {akeeba_ajax_error_handler|null} errorCallback Callback for displaying errors
	 * @param {boolean} useCaching Should I let the browser apply caching to the request? Default: false.
	 * @param {int} timeout Timeout or the request in milliseconds (default: 600000 = 600 seconds).
	 * @param {boolean} usejson True to JSON-decode the AJAX result (default: true)
	 */
	this.call = function (data, successCallback, errorCallback, useCaching, timeout, usejson)
	{
		if (useCaching == null)
		{
			useCaching = false;
		}

		if (usejson == null)
		{
			usejson = false;
		}

		if (timeout == null)
		{
			timeout = 10000;

			if (usejson)
			{
				timeout = 600000;
			}
		}

		// When we want to disable caching we have to add a unique URL parameter
		if (!useCaching)
		{
			var now             = new Date().getTime() / 1000;
			var s               = parseInt(now, 10);
			data._dontcachethis = Math.round((now - s) * 1000) / 1000;
		}

		// Make sure we have a URL
		if (this.url == null)
		{
			this.url = 'index.php';
		}

		// Set up the method
		var method = "POST";

		if (typeof(data._akeeba_ajax_method) !== 'undefined')
		{
			method = data._akeeba_ajax_method.toUpperCase();
			delete data['_akeeba_ajax_method'];
		}

		var that = this;

		var structure =
				{
					type:    "POST",
					url:     this.url,
					cache:   false,
					data:    data,
					timeout: 600000,
					success: function (msg)
							 {
								 // Initialize
								 var junk         = null;
								 var message      = "";
								 var valid_pos    = 0;
								 var responseData = msg;

								 if (usejson)
								 {
									 // Get rid of junk before the data
									 valid_pos = msg.indexOf('###');

									 if (valid_pos === -1)
									 {
										 // Valid data not found in the response
										 msg = 'Invalid response: ' + msg;
										 errorCallback(msg);

										 return;
									 }

									 message = msg;

									 if (valid_pos !== 0)
									 {
										 // Data is prefixed with junk
										 junk    = msg.substr(0, valid_pos);
										 message = msg.substr(valid_pos);
									 }

									 message = message.substr(3); // Remove triple hash in the beginning

									 // Get of rid of junk after the data
									 valid_pos = message.lastIndexOf('###');
									 message   = message.substr(0, valid_pos); // Remove triple hash in the end

									 try
									 {
										 responseData = JSON.parse(message);
									 }
									 catch (err)
									 {
										 msg = err.message + "\n<br/>\n<pre>\n" + message + "\n</pre>";
										 errorCallback(msg);

										 return;
									 }
								 }

								 // Call the callback function
								 successCallback(responseData);
							 },
					error:   function (Request, textStatus, errorThrown)
							 {
								 var message = '<strong>HTTP Request Error</strong><br/>HTTP Status: ' + Request.status + ' (' + Request.statusText + ')<br/>';
								 message     = message + 'Internal status: ' + textStatus + '<br/>';
								 message     = message + 'XHR ReadyState: ' + Request.readyState + '<br/>';
								 message     = message + 'Raw server response:<br/>' + Request.responseText;

								 errorCallback(message);
							 }
				};

		window.jQuery.ajax(structure);
	};

	/**
	 * Run a JSON AJAX request
	 *
	 * @param {object} data The data to send. Use _akeeba_ajax_method to define POST (default) or GET.
	 * @param {akeeba_ajax_message_handler} successCallback Callback for successful AJAX requests
	 * @param {akeeba_ajax_error_handler|null} errorCallback Callback for displaying errors
	 * @param {boolean} useCaching Should I let the browser apply caching to the request? Default: false.
	 * @param {int} timeout Timeout or the request in milliseconds (default: 600000 = 600 seconds).
	 */
	this.callJSON = function (data, successCallback, errorCallback, useCaching, timeout)
	{
		this.call(data, successCallback, errorCallback, useCaching, timeout, true);
	};

	/**
	 * Run a plain text (non-JSON) AJAX request
	 *
	 * @param {object} data The data to send. Use _akeeba_ajax_method to define POST (default) or GET.
	 * @param {akeeba_ajax_message_handler} successCallback Callback for successful AJAX requests
	 * @param {akeeba_ajax_error_handler|null} errorCallback Callback for displaying errors
	 * @param {boolean} useCaching Should I let the browser apply caching to the request? Default: false.
	 * @param {int} timeout Timeout or the request in milliseconds (default: 600000 = 600 seconds).
	 */
	this.callRaw = function (data, successCallback, errorCallback, useCaching, timeout)
	{
		this.call(data, successCallback, errorCallback, useCaching, timeout, false);
	};

	this.dummy_error_handler = function (error)
	{
		alert("An error has occured\n" + error);
	}
};