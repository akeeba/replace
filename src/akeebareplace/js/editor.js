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

if (typeof akeeba.replace === 'undefined')
{
	akeeba.replace = {
		editor:        {},
		tablesAjaxURL: '',
		strings:       {
			'lblKey':    '',
			'lblValue':  '',
			'lblDelete': ''
		}
	};
}

/**
 * Render the replacements key-value editor
 *
 * @param   {Element}  container  The container element (e.g. div) of the editor to render
 * @param   {object}   data       The data to render the editor with
 */
akeeba.replace.editor.render = function (container, data)
{
	// Get the row container from the selector
	var elContainer = window.jQuery(container);

	// Store the key-value information as a data property
	elContainer.data('keyValueData', data);

	// Render one GUI row per data row
	for (var valFrom in data)
	{
		// Skip if the key is a property from the object's prototype
		if (!data.hasOwnProperty(valFrom)) continue;

		var valTo = data[valFrom];

		if ((valFrom === '') && (valTo === ''))
		{
			continue;
		}

		akeeba.replace.editor.renderRow(elContainer, valFrom, valTo);
	}

	// Add the last, empty row
	akeeba.replace.editor.renderRow(elContainer, "", "");
};

/**
 * Render a single row of the key-value editor
 *
 * @param   {Element}  elContainer  The container element of the editor
 * @param   {string}   valFrom      "From" value (left hand side)
 * @param   {string}   valTo        "To" value (right hand side)
 */
akeeba.replace.editor.renderRow = function (elContainer, valFrom, valTo)
{
	var elRow = window.jQuery("<div />").addClass("akeeba-container--75-25");

	var elFromInput = window.jQuery("<input />")
							.addClass("akeebareplace-keyValueFrom")
							.attr("type", "text")
							.attr("title", akeeba.replace.strings["lblKey"])
							.attr("placeholder", akeeba.replace.strings["lblKey"])
							.val(valFrom);

	var elToInput = window.jQuery("<input />")
						  .addClass("akeebareplace-keyValueTo")
						  .attr("type", "text")
						  .attr("title", akeeba.replace.strings["lblValue"])
						  .attr("placeholder", akeeba.replace.strings["lblValue"])
						  .val(valTo);

	var elDeleteIcon = window.jQuery("<span />")
							 .addClass("akion-trash-a");

	var elDeleteButton = window.jQuery("<span />")
							   .addClass("akeeba-btn--small--red akeebareplace-keyValueButtonDelete")
							   .addClass("title", akeeba.replace.strings["lblDelete"])
							   .append(elDeleteIcon);

	var elUpIcon = window.jQuery("<span />")
						 .addClass("akion-chevron-up");

	var elUpButton = window.jQuery("<span />")
						   .addClass("akeeba-btn--small akeebareplace-keyValueButtonUp")
						   .append(elUpIcon);

	var elDownIcon = window.jQuery("<span />")
						   .addClass("akion-chevron-down");

	var elDownButton = window.jQuery("<span />")
							 .addClass("akeeba-btn--small akeebareplace-keyValueButtonDown")
							 .append(elDownIcon);

	var elInputWrapper = window.jQuery("<div />").addClass('akeeba-container--50-50')
							   .append(elFromInput)
							   .append(elToInput);

	var elButtonWrapper = window.jQuery("<div />")
								.append(elDeleteButton)
								.append(elUpButton)
								.append(elDownButton);

	elFromInput.blur(function (e)
	{
		akeeba.replace.editor.reflow(elContainer);
	});

	elToInput.blur(function (e)
	{
		akeeba.replace.editor.reflow(elContainer);
	});

	elDeleteButton.click(function (e)
	{
		elFromInput.val("");
		elToInput.val("");
		akeeba.replace.editor.reflow(elContainer);
	});

	elUpButton.click(function (e)
	{
		var elPrev = elRow.prev();

		if (!elPrev.length)
		{
			return;
		}

		var elPrevFrom = elPrev.find('.akeebareplace-keyValueFrom');
		var elPrevTo   = elPrev.find('.akeebareplace-keyValueTo');

		var prevFrom = elPrevFrom.val();
		var prevTo   = elPrevTo.val();

		elPrevFrom.val(elFromInput.val());
		elPrevTo.val(elToInput.val());
		elFromInput.val(prevFrom);
		elToInput.val(prevTo);

		akeeba.replace.editor.reflow(elContainer);
	});

	elDownButton.click(function (e)
	{
		var elNext = elRow.next();

		if (!elNext.length)
		{
			return;
		}

		var elNextFrom = elNext.find('.akeebareplace-keyValueFrom');
		var elNextTo   = elNext.find('.akeebareplace-keyValueTo');

		var nextFrom = elNextFrom.val();
		var nextTo   = elNextTo.val();

		elNextFrom.val(elFromInput.val());
		elNextTo.val(elToInput.val());
		elFromInput.val(nextFrom);
		elToInput.val(nextTo);

		akeeba.replace.editor.reflow(elContainer);
	});

	elRow.append(elInputWrapper, elButtonWrapper);
	elContainer.append(elRow);
};

/**
 * Reflow the editor
 *
 * @param   {Element}  elContainer  The container element of the editor
 */
akeeba.replace.editor.reflow = function (elContainer)
{
	var data        = {};
	var strFrom     = "";
	var strTo       = "";
	var elRows      = elContainer.children();
	var hasEmptyRow = false;

	// Convert rows to a data object
	window.jQuery.each(elRows, function (idx, elRow)
	{
		var $elRow  = window.jQuery(elRow);
		var valFrom = $elRow.find('.akeebareplace-keyValueFrom').val();
		var valTo   = $elRow.find('.akeebareplace-keyValueTo').val();

		// If the From value is empty I may have to delete this row
		if (valFrom === '')
		{
			if (idx >= elRows.length)
			{
				// This is the last empty row. Do not remove and set the flag of having a last empty row.
				hasEmptyRow = true;

				return;
			}

			// This is an empty From in a row other than the last. Remove it.
			$elRow.remove();

			return;
		}

		data[valFrom] = valTo;
		strFrom += "\n" + valFrom;
		strTo += "\n" + valTo;
	});

	// If I don't have a last empty row, create one
	if (!hasEmptyRow)
	{
		akeeba.replace.editor.renderRow(elContainer, "", "");
	}

	// Store the key-value information as a data property
	window.jQuery(elContainer).data('keyValueData', data);

	// Transfer the data to the textboxes
	var elFrom = window.jQuery(elContainer).data('fromElement');
	var elTo   = window.jQuery(elContainer).data('toElement');
	window.jQuery(elFrom).val(strFrom.replace(/^\s+/g, ""));
	window.jQuery(elTo).val(strTo.replace(/^\s+/g, ""));
};

/**
 * Displays the Javascript powered key-value editor
 *
 * @param   {Element}  editorContainer    The container element for the GUI editor
 * @param   {Element}  textareaContainer  The container element with the text area inputs
 */
akeeba.replace.showEditor = function (editorContainer, textareaContainer)
{
	var elContainer = window.jQuery(editorContainer);
	var elFrom      = window.jQuery(textareaContainer).find('textarea').first();
	var elTo        = window.jQuery(textareaContainer).find('textarea:eq(1)').first();

	elContainer.data('fromElement', elFrom);
	elContainer.data('toElement', elTo);

	var from            = elFrom.val().split("\n");
	var to              = elTo.val().split("\n");
	var extractedValues = {};

	for (var i = 0; i < Math.min(from.length, to.length); i++)
	{
		extractedValues[from[i]] = to[i];
	}

	elContainer.show();
	window.jQuery(textareaContainer).hide();
	akeeba.replace.editor.render(elContainer, extractedValues);
};

/**
 * Show or hide the advanced options
 *
 * @param   {string}  panelID  The ID attribute of the advacned options panel
 */
akeeba.replace.showOptions = function (panelID)
{
	var elPanel        = document.getElementById(panelID);
	var currentDisplay = elPanel.style.display;

	if (currentDisplay === 'none')
	{
		elPanel.style.display = 'block';
		window.location.hash  = '#' + panelID;

		return;
	}

	elPanel.style.display = 'none';
	window.location.hash  = '';
};

akeeba.replace.onAllTablesChange = function ()
{
	// Store current exclusions
	var currentExclusions = [];
	var elSelect          = document.getElementById('akeebareplaceExcludeTables');
	var allOptions        = elSelect.querySelectorAll('option');
	var strNone           = '- none -';

	for (var i = 0; i < allOptions.length; i++)
	{
		var thisOption = allOptions[i];

		if (thisOption.value === '')
		{
			strNone = thisOption.innerText;
		}

		if (thisOption.selected)
		{
			currentExclusions.push(thisOption.value);
		}
	}

	// Do an AJAX request
	console.info(akeeba.replace.tablesAjaxURL);
	var ajax = new AkeebaReplaceAjax(akeeba.replace.tablesAjaxURL);
	ajax.callJSON({
		'_akeeba_ajax_method': 'GET',
		'allTables': document.getElementById('akeebareplace-allTables').checked ? 1 : 0
	}, function (newTables)
	{
		elSelect.innerHTML = '';

		var elRow = document.createElement('option');
		elRow.value = '';
		elRow.innerText = strNone;

		if (currentExclusions.indexOf('') > -1)
		{
			elRow.selected = true;
		}

		elSelect.appendChild(elRow);

		for (var j = 0; j < newTables.length; j++)
		{
			var thisTable = newTables[j];

			elRow = document.createElement('option');
			elRow.value = thisTable;
			elRow.innerText = thisTable;

			if (currentExclusions.indexOf(thisTable) > -1)
			{
				elRow.selected = true;
			}

			elSelect.appendChild(elRow);
		}

	}, function (msg) {
		alert(msg);
	}, false, 10000);
};