/*
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

// Object initialization
if (typeof(akeeba) === "undefined")
{
	var akeeba = {};
}

if (typeof akeeba.replace === "undefined")
{
	akeeba.replace = {
		editor: {},
		tablesAjaxURL: "",
		strings: {
			"lblKey": "",
			"lblValue": "",
			"lblDelete": ""
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
	// Store the key-value information as a data property
	akeeba.System.data.set(container, "keyValueData", data);

	// Render one GUI row per data row
	for (var valFrom in data)
	{
		// Skip if the key is a property from the object's prototype
		if (!data.hasOwnProperty(valFrom))
		{
			continue;
		}

		var valTo = data[valFrom];

		if ((valFrom === "") && (valTo === ""))
		{
			continue;
		}

		akeeba.replace.editor.renderRow(container, valFrom, valTo);
	}

	// Add the last, empty row
	akeeba.replace.editor.renderRow(container, "", "");
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
	var elRow       = document.createElement("div");
	elRow.className = "akeeba-container--75-25";

	var elFromInput         = document.createElement("input");
	elFromInput.className   = "akeebareplace-keyValueFrom";
	elFromInput.type        = "text";
	elFromInput.title       = akeeba.replace.strings["lblKey"];
	elFromInput.placeholder = akeeba.replace.strings["lblKey"];
	elFromInput.value       = valFrom;

	var elToInput         = document.createElement("input");
	elToInput.className   = "akeebareplace-keyValueTo";
	elToInput.type        = "text";
	elToInput.title       = akeeba.replace.strings["lblValue"];
	elToInput.placeholder = akeeba.replace.strings["lblValue"];
	elToInput.value       = valTo;

	var elDeleteIcon       = document.createElement("span");
	elDeleteIcon.className = "akion-trash-a";

	var elDeleteButton       = document.createElement("span");
	elDeleteButton.className = "akeeba-btn--small--red akeebareplace-keyValueButtonDelete";
	elDeleteButton.title     = akeeba.replace.strings["lblDelete"];
	elDeleteButton.appendChild(elDeleteIcon);

	var elUpIcon       = document.createElement("span");
	elUpIcon.className = "akion-chevron-up";

	var elUpButton       = document.createElement("span");
	elUpButton.className = "akeeba-btn--small akeebareplace-keyValueButtonUp";
	elUpButton.appendChild(elUpIcon);

	var elDownIcon       = document.createElement("span");
	elDownIcon.className = "akion-chevron-down";

	var elDownButton       = document.createElement("span");
	elDownButton.className = "akeeba-btn--small akeebareplace-keyValueButtonDown";
	elDownButton.appendChild(elDownIcon);

	var elInputWrapper       = document.createElement("div");
	elInputWrapper.className = "akeeba-container--50-50";
	elInputWrapper.appendChild(elFromInput);
	elInputWrapper.appendChild(elToInput);

	var elButtonWrapper = document.createElement("div");
	elButtonWrapper.appendChild(elDeleteButton);
	elButtonWrapper.appendChild(elUpButton);
	elButtonWrapper.appendChild(elDownButton);

	akeeba.System.addEventListener(elFromInput, "blur", function (e)
	{
		akeeba.replace.editor.reflow(elContainer);
	});

	akeeba.System.addEventListener(elToInput, "blur", function (e)
	{
		akeeba.replace.editor.reflow(elContainer);
	});

	akeeba.System.addEventListener(elDeleteButton, "click", function (e)
	{
		elFromInput.value = "";
		elToInput.value   = "";
		akeeba.replace.editor.reflow(elContainer);
	});

	akeeba.System.addEventListener(elUpButton, "click", function (e)
	{
		var elPrev = this.parentElement.parentElement.previousSibling;

		if (elPrev === null)
		{
			return;
		}

		var elPrevFrom = elPrev.querySelector(".akeebareplace-keyValueFrom");
		var elPrevTo   = elPrev.querySelector(".akeebareplace-keyValueTo");

		var prevFrom = elPrevFrom.value;
		var prevTo   = elPrevTo.value;

		elPrevFrom.value  = elFromInput.value;
		elPrevTo.value    = elToInput.value;
		elFromInput.value = prevFrom;
		elToInput.value   = prevTo;

		akeeba.replace.editor.reflow(elContainer);
	});

	akeeba.System.addEventListener(elDownButton, "click", function (e)
	{
		var elNext = this.parentElement.parentElement.nextSibling;

		if (elNext === null)
		{
			return;
		}

		var elNextFrom = elNext.querySelector(".akeebareplace-keyValueFrom");
		var elNextTo   = elNext.querySelector(".akeebareplace-keyValueTo");

		var nextFrom = elNextFrom.value;
		var nextTo   = elNextTo.value;

		elNextFrom.value  = elFromInput.value;
		elNextTo.value    = elToInput.value;
		elFromInput.value = nextFrom;
		elToInput.value   = nextTo;

		akeeba.replace.editor.reflow(elContainer);
	});

	elRow.appendChild(elInputWrapper);
	elRow.appendChild(elButtonWrapper);
	elContainer.appendChild(elRow);
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
	var elRows      = elContainer.childNodes;
	var hasEmptyRow = false;

	// Convert rows to a data object

	for (var i = 0; i < elRows.length; i++)
	{
		var elRow = elRows[i];

		var valFrom = elRow.querySelector(".akeebareplace-keyValueFrom").value;
		var valTo   = elRow.querySelector(".akeebareplace-keyValueTo").value;

		// If the From value is empty I may have to delete this row
		if (valFrom === "")
		{
			if (i === (elRows.length - 1))
			{
				// This is the last empty row. Do not remove and set the flag of having a last empty row.
				hasEmptyRow = true;

				continue;
			}

			// This is an empty From in a row other than the last. Remove it.
			elRow.parentNode.removeChild(elRow);

			continue;
		}

		data[valFrom] = valTo;
		strFrom += "\n" + valFrom;
		strTo += "\n" + valTo;
	}

	// If I don't have a last empty row, create one
	if (!hasEmptyRow)
	{
		akeeba.replace.editor.renderRow(elContainer, "", "");
	}

	// Store the key-value information as a data property
	akeeba.System.data.set(elContainer, "keyValueData", data);

	// Transfer the data to the textboxes
	var elFrom = document.getElementById(akeeba.System.data.get(elContainer, "fromElement"));
	var elTo   = document.getElementById(akeeba.System.data.get(elContainer, "toElement"));

	elFrom.value = strFrom.replace(/^\s+/g, "");
	elTo.value   = strTo.replace(/^\s+/g, "");
};

/**
 * Displays the Javascript powered key-value editor
 *
 * @param   {Element}  editorContainer    The container element for the GUI editor
 * @param   {Element}  textareaContainer  The container element with the text area inputs
 */
akeeba.replace.showEditor = function (editorContainer, textareaContainer)
{
	var textAreas = textareaContainer.querySelectorAll("textarea");
	var elFrom    = textAreas[0];
	var elTo      = textAreas[1];

	akeeba.System.data.set(editorContainer, "fromElement", elFrom.id);
	akeeba.System.data.set(editorContainer, "toElement", elTo.id);

	var from            = elFrom.value.split("\n");
	var to              = elTo.value.split("\n");
	var extractedValues = {};

	for (var i = 0; i < Math.min(from.length, to.length); i++)
	{
		extractedValues[from[i]] = to[i];
	}

	editorContainer.style.display   = "block";
	textareaContainer.style.display = "none";
	akeeba.replace.editor.render(editorContainer, extractedValues);
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

	if (currentDisplay === "none")
	{
		elPanel.style.display = "block";
		window.location.hash  = "#" + panelID;

		return;
	}

	elPanel.style.display = "none";
	window.location.hash  = "";
};

akeeba.replace.onAllTablesChange = function ()
{
	// Store current exclusions
	var currentExclusions = [];
	var elSelect          = document.getElementById("akeebareplaceExcludeTables");
	var allOptions        = elSelect.querySelectorAll("option");
	var strNone           = "- none -";

	for (var i = 0; i < allOptions.length; i++)
	{
		var thisOption = allOptions[i];

		if (thisOption.value === "")
		{
			strNone = thisOption.innerText;
		}

		if (thisOption.selected)
		{
			currentExclusions.push(thisOption.value);
		}
	}

	// Do an AJAX request
	akeeba.System.params.AjaxURL = akeeba.replace.tablesAjaxURL;
	akeeba.System.doAjax({
		"_akeeba_ajax_method": "GET",
		"allTables": document.getElementById("akeebareplace-allTables").checked ? 1 : 0
	}, function (newTables)
	{
		elSelect.innerHTML = "";

		var elRow       = document.createElement("option");
		elRow.value     = "";
		elRow.innerText = strNone;

		if (currentExclusions.indexOf("") > -1)
		{
			elRow.selected = true;
		}

		elSelect.appendChild(elRow);

		for (var j = 0; j < newTables.length; j++)
		{
			var thisTable = newTables[j];

			elRow           = document.createElement("option");
			elRow.value     = thisTable;
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