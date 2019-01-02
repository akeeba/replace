/*
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
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
		switchingTasks: false,
		nonces: {
			'-1': '',
			'delete': '',
			'deleteFiles': ''
		}
	};
}

akeeba.replace.onBulkChange = function(e)
{
	if (akeeba.replace.switchingTasks)
	{
		return;
	}

	akeeba.replace.switchingTasks = true;

	var otherID = 'bulk-action-selector-top';

	if (this.id === 'bulk-action-selector-top')
	{
		otherID = 'bulk-action-selector-bottom';
	}

	document.getElementById(otherID).value = this.value;
	document.getElementById('akeebareplace-task').value = this.value;
	document.getElementById('akeebareplace-nonce').value = akeeba.replace.nonces[this.value];

	akeeba.replace.switchingTasks = false;
};

akeeba.System.documentReady(function() {
	akeeba.System.addEventListener(document.getElementById('bulk-action-selector-top'), 'change', akeeba.replace.onBulkChange);
	akeeba.System.addEventListener(document.getElementById('bulk-action-selector-bottom'), 'change', akeeba.replace.onBulkChange);
});