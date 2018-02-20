// ========================================================
// = general.js - Basic general purpose scripting methods =
// ========================================================
// Author: Theo Howell
// Last Modified: 28/09/2012

function el_id(id)
{
	return document.getElementById(id);
}

function supports_input_placeholder() 
{
	var i = document.createElement('input');
	return 'placeholder' in i;
}

function debugln(msg)
{
	if ( ! window.console ) 
		console = { log: function(){} };
	console.log(msg);
	console.error()
}

function confirmDelete()
{
	return confirm("Are you sure you wish to delete this item?");
}

function confirmDeleteSubmit()
{
	if (confirm("Are you sure you wish to delete this item?"))
        document.mainForm.submit();
}

// ===================
// = Class additions =
// ===================

if (typeof String.prototype.endsWith != 'function') 
{
	String.prototype.endsWith = function(suffix) {
	    return this.indexOf(suffix, this.length - suffix.length) !== -1;
	};
}

if (typeof String.prototype.startsWith != 'function') 
{
	String.prototype.startsWith = function(prefix) { 
		return this.slice(-prefix.length) == prefix;
	};
}

if (typeof String.prototype.trim != 'function')
{
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g, '');
	};
}
