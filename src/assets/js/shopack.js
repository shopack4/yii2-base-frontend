function array_isset(arr) {
	var i, max_i;
	for (i = 1, max_i = arguments.length; i < max_i; i++) {
		arr = arr[arguments[i]];
		if (arr === undefined) {
			return false;
		}
	}
	return true;
}

function showWaitPanel(containerID=null, waitPanelID='waitpanel')
{
	$('#' + waitPanelID).show();
}

function hideWaitPanel(containerID=null, waitPanelID='waitpanel')
{
	$('#' + waitPanelID).hide();
}
function changeLastNumberPartOfString(code, increase=true, nullValue='0')
{
// console.log(code);
	if ((code == null) || (code === undefined) || (code === ''))
		return nullValue;

	var parts = code.split(/(\d+)/);
	// console.log(parts);
	var count = parts.length-1;
	if (parts[count] == '')
		--count;
	var newVal = '';
	var numberFound = false;
	for (i=count; i>=0; i--)
	{
		if (numberFound)
			newVal = parts[i] + newVal;
		else
		{
			if (!isNaN(parts[i]))
			{
				numberFound = true;
				var num = Number(parts[i]);
				if (increase)
					newVal = String(num+1) + newVal;
				else if(num > 0)
					newVal = String(num-1) + newVal;
				else
					newVal = String(num) + newVal;
			}
			else
			{
				newVal = parts[i] + newVal;
			}
		}
	}
	if (!numberFound)
		newVal = newVal + nullValue;
	return newVal;
}
function increaseLastNumberPartOfString(code, nullValue='0')
{
	return changeLastNumberPartOfString(code, true, nullValue);
}
function decreaseLastNumberPartOfString(code, nullValue='0')
{
	return changeLastNumberPartOfString(code, false, nullValue);
}
function replaceUrlParam(url, paramName, paramValue)
{
	var pattern = new RegExp('(\\\?|\\\&)(' + paramName + '=).*?(&|$)');
	var newUrl = url;
	if (url.search(pattern) >= 0)
	{
		//console.log('found');
		newUrl = url.replace(pattern, '$1$2' + paramValue + '$3');
	}
	else
	{
		newUrl = newUrl + (newUrl.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
	}
	return newUrl;
}

//https://stackoverflow.com/questions/2200494/jquery-trigger-event-when-an-element-is-removed-from-the-dom
$.cleanData = (function(orig) {
	return function(elems) {
		var events, elem, i;
		for (i = 0; (elem = elems[ i ]) != null; i++) {
			try {
				// Only trigger remove when necessary to save time
				events = $._data(elem, 'events');
				if (events && events.remove)
					$(elem).triggerHandler('remove');
			// Http://bugs.jquery.com/ticket/8235
			} catch (e) {}
		}
		orig(elems);
	};
})($.cleanData);

//https://stackoverflow.com/questions/8486099/how-do-i-parse-a-url-query-parameters-in-javascript
function getJsonFromUrl(url) {
	if (!url)
		url = location.href;
	var question = url.indexOf('?');
	var hash = url.indexOf('#');
	if (hash==-1 && question==-1)
		return {};
	if (hash==-1)
		hash = url.length;
	var query = (question==-1 || hash==question+1) ? url.substring(hash) : url.substring(question+1, hash);
	var result = {};
	query.split('&').forEach(function(part) {
		if (!part)
			return;
		part = part.split('+').join(' '); // replace every + with space, regexp-free version
		var eq = part.indexOf('=');
		var key = eq>-1 ? part.substr(0, eq) : part;
		var val = eq>-1 ? decodeURIComponent(part.substr(eq+1)) : '';
		var from = key.indexOf('[');
		if (from==-1)
			result[decodeURIComponent(key)] = val;
		else
		{
			var to = key.indexOf(']', from);
			var index = decodeURIComponent(key.substring(from+1, to));
			key = decodeURIComponent(key.substring(0, from));
			if (!result[key])
				result[key] = [];
			if (!index)
				result[key].push(val);
			else
				result[key][index] = val;
		}
	});
	return result;
}

//leaflet.on('show'
$(function() {
	$.each(['show', 'hide', 'toggleClass', 'addClass', 'removeClass'], function() {
		var _oldFn = $.fn[this];
		$.fn[this] = function() {
			var hidden = this.find(':hidden').add(this.filter(':hidden'));
			var visible = this.find(':visible').add(this.filter(':visible'));
			var result = _oldFn.apply(this, arguments);
			hidden.filter(':visible').each(function() {
				$(this).triggerHandler('show');
			});
			visible.filter(':hidden').each(function() {
				$(this).triggerHandler('hide');
			});
			return result;
		}
	});
});
/*
function fitText(outputSelector) {
	// max font size in pixels
	const maxFontSize = 50;
	// get the DOM output element by its selector
	let outputDiv = document.getElementById(outputSelector);
	// get element's width
	let width = outputDiv.clientWidth;
	// get content's width
	let contentWidth = outputDiv.scrollWidth;
	// get fontSize
	let fontSize = parseInt(window.getComputedStyle(outputDiv, null).getPropertyValue('font-size'),10);
	// if content's width is bigger then elements width - overflow
	if (contentWidth > width){
			fontSize = Math.ceil(fontSize * width/contentWidth,10);
			fontSize =  fontSize > maxFontSize  ? fontSize = maxFontSize  : fontSize - 1;
			outputDiv.style.fontSize = fontSize+'px';
	}else{
			// content is smaller then width... let's resize in 1 px until it fits
			while (contentWidth === width && fontSize < maxFontSize){
					fontSize = Math.ceil(fontSize) + 1;
					fontSize = fontSize > maxFontSize  ? fontSize = maxFontSize  : fontSize;
					outputDiv.style.fontSize = fontSize+'px';
					// update widths
					width = outputDiv.clientWidth;
					contentWidth = outputDiv.scrollWidth;
					if (contentWidth > width){
							outputDiv.style.fontSize = fontSize-1+'px';
					}
			}
	}
}
*/
