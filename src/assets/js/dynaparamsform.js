// console.log('createDynamicParamsFormUI 1.2');

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

function createDynamicParamsFormUI(
	selectedID,
	loadingText,
	getParamsUrl,
	formID,
	paramID,
	formName,
	paramName,
	initialData,
	paramsContainerID,
	labelSpan = 2,
	emptyParamsHint = null
) {
	var paramsContainer = $('#' + paramsContainerID);
	paramsContainer.show();
	paramsContainer.empty();
	paramsContainer.html(loadingText);

	if (getParamsUrl.indexOf('__SEL__') >= 0)
		getParamsUrl = getParamsUrl.replace('__SEL__', selectedID);
	else
		getParamsUrl = getParamsUrl + selectedID;

	// console.log({getParamsUrl, initialData});

	$.ajax(
		getParamsUrl,
		{
			dataType: 'json',
			method: 'POST'
		})
		.done(function(response) {
// console.log(response);
			if (response.count == 0) {
				// paramsContainer.html("<div style='text-align:center'>فاقد پارامتر.</div>");
				if ((emptyParamsHint != null) && (emptyParamsHint != ''))
					paramsContainer.html(emptyParamsHint);
				else {
					paramsContainer.empty();
					paramsContainer.hide();
				}
			} else {
				out = '';
				response.list.forEach(function(val, index, arr) {
					if (formID == null)
						_id = paramID + "-" + val['id'];
					else
						_id = formID + "-" + paramID + "-" + val['id'];

					if (formName == null)
						_name = paramName + "[" + val['id'] + "]";
					else
						_name = formName + "[" + paramName + "][" + val['id'] + "]";

					out += createDynamicParamsFormField(val, _id, _name, initialData, labelSpan);
				}, out);
				paramsContainer.html(out);
			}
		})
		.fail(function(jqXHR, exception) {
			// Our error logic here
			var msg = '';
			if (jqXHR.status === 0)
				msg = 'Not connect. Verify Network.';
			else if (jqXHR.status == 404)
				msg = 'Requested page not found. [404]';
			else if (jqXHR.status == 500)
				msg = 'Internal Server Error [500].';
			else if (exception === 'parsererror')
				msg = 'Requested JSON parse failed.';
			else if (exception === 'timeout')
				msg = 'Time out error.';
			else if (exception === 'abort')
				msg = 'Ajax request aborted.';
			else
				msg = 'Uncaught Error.' + jqXHR.responseText;
			paramsContainer.html('<pre>' + msg + '<br>' + jqXHR.responseText + '</pre>');
		})
	;
}

function createDynamicParamsFormField(
	val,
	_id,
	_name,
	initialData,
	labelSpan = 2
) {
	var init_val = null;
	if (initialData !== undefined) {
		if (initialData[val['id']] !== undefined)
			init_val = initialData[val['id']];
		else if (initialData[_name] !== undefined)
			init_val = initialData[_name];
	}

	if ((init_val == null) && (val['init_val'] !== undefined))
		init_val = val['init_val'];

	var valueSpan = 12 - labelSpan;

	var div = '';

	if (val['type'] == 'section') {
		div += "<h4 class='form-section'>";
		div += val['label'];
		div += "</h4>";
	} else {
		div += "<div class='mb-3 row highlight-addon form-group field-" + _id + "'>";
		div += "<label class='col-form-label col-md-" + labelSpan + "' for='" + _id + "'>" + val['label'] + "</label>";
		div += "<div class='col-md-" + valueSpan + "'>";

		if (array_isset(val, 'fieldOptions', 'addon'))
			div += "<div class='input-group'>";

		if (array_isset(val, 'fieldOptions', 'addon', 'prepend')) {
			var v = val['fieldOptions']['addon']['prepend'];
			if (v['content'] !== undefined) {
				div += "<span class='input-group-text'>" + v['content'] + "</span>";
			} else {
				for (var i=0; i<v.length; i++) {
					var vv = v[i];
					div += "<span class='input-group-text'>" + vv['content'] + "</span>";
				}
			}
		}

		if ((val['type'] == 'string')
			|| (val['type'] == 'text')
			|| (val['type'] == 'password')
			|| (val['type'] == 'number')
		) {
			div += "<input type='" + (val['type'] == 'password' ? 'password' : 'text') + "' class='form-control'"
				+ "id='" + _id + "' "
				+ "name='" + _name + "'";
			if (init_val != null)
				div += " value='" + init_val + "'";
			else if (val['default'] !== undefined)
				div += " value='" + val['default'] + "'";

			var style = '';
			if (val['style'] !== undefined)
				style = val['style'];

			if (val['type'] == 'number') {
				if (style != '')
					style += ';';
				style += 'direction:ltr;';
			}

			if (style != '')
				div += " style='" + style + "'";

			div += ">";
		} else if ((val['type'] == 'multi-string') || (val['type'] == 'multi-text')) {
			div += "<textarea class='form-control'"
				+ "id='" + _id + "' "
				+ "name='" + _name + "'";

			if (val['style'] !== undefined)
				div += " style='" + val['style'] + "'";

			if (val['rows'] !== undefined)
				div += " rows='" + val['rows'] + "'";

			div += ">";

			if (init_val != null)
				div += init_val;
			else if (val['default'] !== undefined)
				div += val['default'];

			div += "</textarea>";
		}
		// else if ((val['type'] == 'bool') || (val['type'] == 'boolean'))
		// {
			// div += "<input type='checkbox' value='1'"
				// + "id='" + _id + "' "
				// + "name='" + _name + "'";
			// if ((init_val != null) && (init_val == '1'))
				// div += " checked";
			// else if ((val['default'] !== undefined) && (val['default'] == true))
				// div += " checked";
			// div += ">";
		// }
		else if ((val['type'] == 'bool') || (val['type'] == 'boolean')) {
			div += "<label class='radio-inline'>"
				+ "<input type='radio' value='1'"
				// + "id='" + _id + "' "
				+ "name='" + _name + "'";

			if ((init_val != null) && (init_val == '1'))
				div += " checked";
			else if ((val['default'] !== undefined) && (val['default'] == 1))
				div += " checked";

			div += ">";
			div += "بلی";
			div += "</label>";

			div += "<label class='radio-inline'>"
				+ "<input type='radio' value='0'"
				// + "id='" + _id + "' "
				+ "name='" + _name + "'";

			if ((init_val != null) && (init_val == '0'))
				div += " checked";
			else if ((val['default'] !== undefined) && (val['default'] == 0))
				div += " checked";

			div += ">";
			div += "خیر";
			div += "</label>";
		} else if ((val['type'] == 'dropdown') || (val['type'] == 'combo')) {
			div += "<select class='form-control'"
				+ "id='" + _id + "' "
				+ "name='" + _name + "'"
				+ ">";
			options = '';
			if (val['allowNone'] !== undefined) {
				options += "<option value=''>" + val['allowNone'] + "</option>";
			}

			data = val['data'];
// console.log(val);
// console.log(init_val);
// console.log(data);
// console.log(initialData);
// console.log(typeof data);
			if (data.length > 0) { //array
				for (i=0; i<data.length; i++) {
					//this is for CategoryModel::getListForDropdown -> browsers reorder array keys
					if (data[i].key === undefined)
						options += "<option value='" + i + "'>" + data[i] + "</option>";
					else
						options += "<option value='" + data[i].key + "'>" + data[i].value + "</option>";
				}
			} else { //object
				for (var v in data) {
					options += "<option value='" + v + "'>" + data[v] + "</option>";
				}
			}

			if (init_val != null)
				options = options.replace('value=\'' + init_val + '\'', 'value=\'' + init_val + '\' selected');
			else if (val['default'] !== undefined)
				options = options.replace('value=\'' + val['default'] + '\'', 'value=\'' + val['default'] + '\' selected');

			div += options;
			div += "</select>";
		} else if (val['type'] == 'radio-list') {
			data = val['data'];
// console.log(val);
// console.log(data);
// console.log(initialData);

			div += "<div class='form-control' style='padding-top:0; padding-bottom:0;'>";
			for (var v in data) {
				div += "<label class='radio-inline'>"
					+ "<input type='radio' value='" + v + "'"
					// + "id='" + _id + "' "
					+ "name='" + _name + "'";

				if ((init_val != null) && (init_val == v))
					div += " checked";
				else if ((val['default'] !== undefined) && (val['default'] == v))
					div += " checked";

				div += ">";
				div += data[v];
				div += "</label>";
			}
			div += "</div>";
		} else if (val['type'] == 'multi-select') {
			data = val['data'];
// console.log(val);
// console.log(data);
// console.log(initialData);
			if (data.length > 0) { //array
				for (i=0; i<data.length; i++) {
					// div += "<input type='checkbox' value='" + i + "'"
						// + "id='" + _id + "' "
						// + "name='" + _name + "'";
					// if ((init_val != null) && (init_val == '1'))
						// div += " checked";
					// else if ((val['default'] !== undefined) && (val['default'] == true))
						// div += " checked";
					// div += ">";

					// options += "<option value='" + i + "'>" + data[i] + "</option>";
				}
			} else { //object
				for (var v in data) {
// if (Array.isArray(val['default']))
// {
// console.log(v);
// console.log(val);
// console.log(Array.isArray(val['default']));
// console.log(val['default'][v]);
// console.log($.inArray(v, val['default']));
// }
					// options += "<option value='" + v + "'>" + data[v] + "</option>";
					div += "<div>";
					div += "<input type='checkbox' value='1'"
						+ "id='" + _id + "-" + v + "' "
						+ "name='" + _name + "[" + v + "]'";

					if (initialData !== undefined) {
						if ((init_val != null) && (init_val[v] !== undefined) && (init_val[v] == '1'))
							div += " checked";
					} else if (val['default'] !== undefined) {
						if ((Array.isArray(val['default'])
									&& ((val['default'][v] !== undefined) || ($.inArray(v, val['default']) != -1)))
								|| (val['default'] === v)
							)
							div += " checked";
					}

					div += ">";
					div += "&nbsp;<label class='control-label' for='" + _id + "-" + v + "'>" + data[v] + "</label>";
					div += "</div>";
				}
			}
		}
	}

	if (array_isset(val, 'fieldOptions', 'addon', 'append')) {
		var v = val['fieldOptions']['addon']['append'];
		if (v['content'] !== undefined) {
			div += "<span class='input-group-text'>" + v['content'] + "</span>";
		} else {
			for (var i=0; i<v.length; i++) {
				var vv = v[i];
				div += "<span class='input-group-text'>" + vv['content'] + "</span>";
			}
		}
	}

	if (array_isset(val, 'fieldOptions', 'addon'))
		div += "</div>";

	// div += "</div>";
	// div += "<div class='col-sm-" + labelSpan + "'>";
	div += "<div class='help-block'></div>";
	// div += "</div>";
	div += "</div>";
	div += "</div>";

	return "<div class='col-md-12'>" + div + "</div>";
		// "<div class='offset-md-" + labelSpan + " col-md-" + (12 - labelSpan) + "'>"
}
