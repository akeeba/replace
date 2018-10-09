/*
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Run a replacement job
 */

// Object initialization
if (typeof(akeeba) === "undefined")
{
	var akeeba = {};
}

if (typeof akeeba.replace === "undefined")
{
	akeeba.replace = {
		logUrl            : "",
		strings           : {
			"lblLastResponse": "Last response from the server: %s seconds ago."
		},
		timeoutTimer      : null,
		resumeTimer       : null,
		resume            : {
			enabled     : true,
			timeout     : 10,
			maxRetries  : 3,
			retry       : 0,
			showWarnings: 0
		}
	};
}

/**
 * Start the timer which launches the next replacement step. This allows us to prevent deep nesting of AJAX calls which
 * could lead to performance issues on long replacement operations.
 *
 * @param   {int}  waitTime  How much time to wait before starting a replacement step, in msec (default: 10)
 */
akeeba.replace.timer = function (waitTime)
{
	if (waitTime <= 0)
	{
		waitTime = 10;
	}

	setTimeout("akeeba.replace.timerTick()", waitTime);
};

/**
 * This is used by the timer() method to run the next replacement step
 */
akeeba.replace.timerTick = function ()
{
	try
	{
		console.log("Timer tick");
	}
	catch (e)
	{
	}

	// Reset the timer
	akeeba.replace.resetTimeoutBar();
	akeeba.replace.startTimeoutBar();

	// Run the step
	akeeba.System.doAjax({
		ajax: "step"
	}, akeeba.replace.onStep, akeeba.replace.onError, false);
};

/**
 * Starts the timer for the last response timer
 */
akeeba.replace.startTimeoutBar = function ()
{
	var lastResponseSeconds = 0;

	akeeba.replace.timeoutTimer = setInterval(function ()
	{
		lastResponseSeconds++;

		var responseTimer = document.querySelector("#akeebareplace-response-timer div.text");

		if (responseTimer)
		{
			responseTimer.textContent = akeeba.replace.strings["lblLastResponse"]
				.replace("%s", lastResponseSeconds.toFixed(0));
		}
	}, 1000);
};

/**
 * Resets the last response timer bar
 */
akeeba.replace.resetTimeoutBar = function ()
{
	try
	{
		clearInterval(akeeba.replace.timeoutTimer);
	}
	catch (e)
	{
	}

	var responseTimer = document.querySelector("#akeebareplace-response-timer div.akeebareplace-text");

	if (responseTimer)
	{
		responseTimer.textContent = akeeba.replace.strings["lblLastResponse"].replace("%s", "0");
	}
};

/**
 * Starts the timer for the retry timer
 */
akeeba.replace.startRetryTimeoutBar = function ()
{
	var remainingSeconds = akeeba.replace.resume.timeout;

	akeeba.replace.resumeTimer = setInterval(function ()
	{
		remainingSeconds--;
		document.getElementById("akeeba-retry-timeout").textContent = remainingSeconds.toFixed(0);

		if (remainingSeconds === 0)
		{
			clearInterval(akeeba.replace.resumeTimer);
			akeeba.replace.resumeReplacement();
		}
	}, 1000);
};

/**
 * Resets the retry timer
 */
akeeba.replace.resetRetryTimeoutBar = function ()
{
	clearInterval(akeeba.replace.resumeTimer);

	document.getElementById("akeeba-retry-timeout").textContent = akeeba.replace.resume.timeout.toFixed(0);
};

/**
 * Start the replacement operation
 */
akeeba.replace.start = function ()
{
	try
	{
		console.log("Starting replacement job");
		console.log(data);
	}
	catch (e)
	{
	}

	document.getElementById("akeebareplace-last-chance").style.display = "none";
	// Show the replacement progress
	document.getElementById("akeebareplace-progress-pane").style.display = "block";

	// Start the response timer
	akeeba.replace.startTimeoutBar();

	var ajax_request = {
		// Data to send to AJAX
		"ajax": "start"
	};

	akeeba.System.doAjax(ajax_request, akeeba.replace.onStep, akeeba.replace.onError, false);
};

/**
 * Replacement operation step callback handler
 *
 * @param   {object}  data  Replacement data received
 */
akeeba.replace.onStep = function (data)
{
	try
	{
		console.log("Running replacement step");
		console.log(data);
	}
	catch (e)
	{
	}

	// Update visual step progress from active domain data
	akeeba.replace.currentDomain = data.Domain;

	// Update step/substep display
	document.getElementById("akeebareplace-step").textContent = data.Step;
	document.getElementById("akeebareplace-substep").textContent = data.Substep;

	// Do we have warnings?
	if (data.Warnings.length > 0)
	{
		for (var i = 0; i < data.Warnings.length; i++)
		{
			warning = data.Warnings[i];

			var newDiv = document.createElement("div");
			newDiv.textContent = warning;
			document.getElementById("akeebareplace-warnings-list").appendChild(newDiv);
		}

		document.getElementById("akeebareplace-warnings-panel").style.display = "block";
	}

	// Do we have errors?
	var error_message = data.Error;

	if (error_message !== "")
	{
		try
		{
			console.error("Got an error message");
			console.log(error_message);
		}
		catch (e)
		{
		}

		// Uh-oh! An error has occurred.
		akeeba.replace.onError(error_message);

		return;
	}

	// No errors. Good! Are we finished yet?
	if (data["HasRun"] === 0)
	{
		try
		{
			console.log("Replacement complete");
			console.log(error_message);
		}
		catch (e)
		{
		}

		// Yes. Show replacement completion page.
		akeeba.replace.onDone();

		return;
	}

	// Reset the retries
	akeeba.replace.resume.retry = 0;

	// How much time do I have to wait?
	var waitTime = 10;

	if (data.hasOwnProperty("sleepTime"))
	{
		waitTime = data.sleepTime;
	}

	// ...and send an AJAX command
	try
	{
		console.log("Starting tick timer with waitTime = " + waitTime + " msec");
	}
	catch (e)
	{
	}

	akeeba.replace.timer(waitTime);
};

/**
 * Resume a replacement attempt after an AJAX error has occurred.
 */
akeeba.replace.resumeReplacement = function ()
{
	// Make sure the timer is stopped
	akeeba.replace.resetRetryTimeoutBar();

	// Hide error and retry panels
	document.getElementById("akeebareplace-error-panel").style.display = "none";
	document.getElementById("akeebareplace-retry-panel").style.display = "none";

	// Show progress and warnings
	document.getElementById("akeebareplace-progress-pane").style.display = "block";

	// Only display warnings if the saved state of warnings is true
	if (akeeba.replace.resume.showWarnings)
	{
		document.getElementById("akeebareplace-warnings-panel").style.display = "block";
	}

	// Restart the replacement
	akeeba.replace.timer();
};

/**
 * Cancel the automatic resumption of a replacement attempt after an AJAX error has occurred
 */
akeeba.replace.cancelResume = function ()
{
	// Make sure the timer is stopped
	akeeba.replace.resetRetryTimeoutBar();

	// Kill the replacement
	var errorMessage = document.getElementById("akeebareplace-error-message-retry").innerHTML;
	akeeba.replace.endWithError(errorMessage);
};

/**
 * AJAX error callback
 *
 * @param   message  The error message received
 */
akeeba.replace.onError = function (message)
{
	// If resume is not enabled, die.
	if (!akeeba.replace.resume.enabled)
	{
		akeeba.replace.endWithError(message);

		return;
	}

	// If we are past the max retries, die.
	if (akeeba.replace.resume.retry >= akeeba.replace.resume.maxRetries)
	{
		akeeba.replace.endWithError(message);

		return;
	}

	// Make sure the timer is stopped
	akeeba.replace.resume.retry++;
	akeeba.replace.resetRetryTimeoutBar();

	// Save display state of warnings panel
	akeeba.replace.resume.showWarnings = (document.getElementById("akeebareplace-warnings-panel").style.display !== "none");

	// Hide progress and warnings
	document.getElementById("akeebareplace-progress-pane").style.display = "none";
	document.getElementById("akeebareplace-warnings-panel").style.display = "none";
	document.getElementById("akeebareplace-error-panel").style.display = "none";

	// Setup and show the retry pane
	document.getElementById("akeebareplace-error-message-retry").innerHTML = message;
	document.getElementById("akeebareplace-retry-panel").style.display = "block";

	// Start the countdown
	akeeba.replace.startRetryTimeoutBar();
};

/**
 * Terminate the replacement with an error
 *
 * @param   message  The error message received
 */
akeeba.replace.endWithError = function (message)
{
	// Make sure the timer is stopped
	akeeba.replace.resetTimeoutBar();

	// Hide progress and warnings
	document.getElementById("akeebareplace-progress-pane").style.display = "none";
	document.getElementById("akeebareplace-warnings-panel").style.display = "none";
	document.getElementById("akeebareplace-retry-panel").style.display = "none";

	// Setup and show error pane
	document.getElementById("akeebareplace-error-message").textContent = message;
	document.getElementById("akeebareplace-error-panel").style.display = "block";
};

/**
 * Replacement finished callback handler
 */
akeeba.replace.onDone = function ()
{
	var rightNow = new Date();

	// Make sure the timer is stopped
	akeeba.replace.resetTimeoutBar();

	// Hide progress
	document.getElementById("akeebareplace-progress-pane").style.display = "none";

	// Show finished pane
	document.getElementById("akeebareplace-complete").style.display = "block";
	document.getElementById("akeebareplace-warnings-panel").style.width = "100%";
};