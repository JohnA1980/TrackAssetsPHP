/**
*
* BLogic
* The Business Logic Web Framework
* 
* @package		BLogic
* @subpackage	Utils
* @version		5
* 
* @license		GPLv3 see license.txt
* @copyright	2010 Sqonk Pty Ltd.
*
*
* This file is distributed
* on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
* express or implied. See the License for the specific language governing
* permissions and limitations under the License.
**/

class blutils
{
	constructor() {
		this.csrfField = "csrfToken";
		this.standardErrorCallback = function(oq, errorText, httpStatus) {
			if (httpStatus != 0) {
				console.error("blogic request error: "+httpStatus+" "+errorText);
				alert(httpStatus+" "+errorText);
			}
            
            if (httpStatus == 401 && errorText == 'Session expired') {
                window.location = window.location;
            }
		}
		
		// Standard error message applied in some of the higher level functions when a communication fault
		// occurs between the client and the server.
		this.standardErrMsg = "There was problem while talking to the server. Please try again later or contact support for assistance.";
		
		// Endpoint for the app server.
		this.serverEndPoint = "index.php";
	}
	
	implode(delim, array) {
	    var combined = "";
	    var len = array.length;
	    for (var i = 0; i < len; i++) {
	        combined += array[i];
	        if (i < len-1)
	            combined += delim;
	    }
	    return combined;
	}
	
	safeJSON(json) {
	    try {
	        var r = JSON.parse(json);
	        return r;
	    } catch (e) {
	        return null;
	    }
	}
	
	e() {
		var elements = [];
		for (let i = 0; i < arguments.length; i++) {
			elements.push(document.getElementById(arguments[i]));
		}
		if (elements.length == 0)
			return null;
		return elements.length == 1 ? elements[0] : elements;
	}
	
	ref(el) {
		var ref = new BLElement(el);
        if (! ref.e()) {
            ref = null;
        }
        return ref;
	}
	
	hide(el) {
		if (typeof el == 'string')
			el = this.e(el);
		if (el)
			el.style.display = "none";
		return el;
	}
	
	show(el, style) {
		if (typeof style == 'undefined')
			style = "block";
		if (typeof el == 'string')
			el = this.e(el);
		if (el)
			el.style.display = style;
		return el;
	}
	
	add(type, parent, props) {
		if (typeof parent == 'string')
			parent = this.e(parent);
		var el = parent.appendChild(document.createElement(type));
		if (typeof props != 'undefined') {
			for (let n in props)
				el.setAttribute(n, props[n]);
		}
		return this.ref(el);
	}
	
	safeValue(array, key, defaultValue) {
		if (typeof defaultValue == 'undefined') {
			defaultValue = '';
		}
		var t = (typeof array).toLowerCase();
		if (t != 'array' && t != 'object') {
			return defaultValue;
		}
		if (! key in array || typeof array[key] == 'undefined') {
			return defaultValue;
		}
		return array[key];
	}
	
	// ========
	// = AJAX =
	// ========
	
	sendRequest(method, url, successCallback, errorCallback) {
		var httpRequest;
		
		if (typeof errorCallback == 'undefined') {
			errorCallback = this.standardErrorCallback;
		}
	
		if ('XMLHttpRequest' in window) // Mozilla, Safari, ...
		{
			httpRequest = new XMLHttpRequest();
	        if (httpRequest.overrideMimeType)
	        	httpRequest.overrideMimeType("application/x-www-form-urlencoded");
		}
		else // IE
		{
			try {
	        	httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
	        } 
	        catch (e) {
		        try {
		            httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
		        } 
		        catch (e) {
					errorCallback(data, "The ActiveXObject for the HTTP request could not be created.");
					return;
				}
	       }
		}

		if (! httpRequest)
		{
			if (errorCallback != null)
				errorCallback(data, "Sorry there was an error communicating with the server.");
			else if (window.console)
				console.log("Sorry there was an error communicating with the server.");
			return false;
		}
        
        // add in X-Requested-With 'XMLHttpRequest'

	    httpRequest.onreadystatechange = function() { 
			if (httpRequest.readyState == 4 && httpRequest.status == 200) {
				successCallback(url, httpRequest.responseText);
			}
			else if (httpRequest.readyState == 4 && errorCallback != null) {
				errorCallback(url, httpRequest.responseText, httpRequest.status);
			}
		}; 
	
	    var fullURL = url;
	    var host = window.location.href;
	    var pos = host.indexOf("http", 0);
	    if (pos != 0)
	    {
	    	if (host.match(".php$" || host.match(".html$")))
	    	{
	    		while (! host.match("/$") && host.length > 1)
	    		{
	    			host = host.substring(0, host.length-1);
	    		}
	    	}
	    	fullURL = host+url;
	    }
		
		httpRequest.open(method, fullURL, true);
        httpRequest.setRequestHeader("X_REQUESTED_WITH", "xmlhttprequest");
		
		return httpRequest;
	} 
		
	get(url, data, success, error) {
		if (data != null && data instanceof Object) {
			var csrf = bl.e(this.csrfField);
			if (csrf)
				data["csrfToken"] = csrf.value;
            data["X_REQUESTED_WITH"] = "xmlhttprequest";
			data = this.params(data);
		}
        else {
            var ajaxReq = "X_REQUESTED_WITH=xmlhttprequest";
            if (data == null)
                data = ajaxReq;
            else
                data += "&"+ajaxReq;
        }
		
		url += "?"+data;
		var request = this.sendRequest('get', url, success, error);
		request.send(null);
	}
	
	post(url, data, success, error) {
		if (data != null && data instanceof Object) {
			var csrf = bl.e(this.csrfField);
			if (csrf)
				data["csrfToken"] = csrf.value;
            data["X_REQUESTED_WITH"] = "xmlhttprequest";
			data = this.params(data);
		}
		else {
            var ajaxReq = "X_REQUESTED_WITH=xmlhttprequest";
            if (data == null)
                data = ajaxReq;
            else
                data += "&"+ajaxReq;
		}        
        
		var request = this.sendRequest('post', url, success, error);
		request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		request.send(data);
	}
	
	post_r(data, callback) {
		bl.post(bl.serverEndPoint, data, function(oq, result) {
			callback(result);
		});
	}
	
	// Standardised json request that suits most of the client-app data restrieval and submission
	// communication.
	json_r(data, callback) {
		var standardJSONResponseHandler = function(oq, result) {
			var json = bl.safeJSON(result);
			if (json == null) {
				console.error(result);
				alert(bl.standardErrMsg);
			}
			else {
				var error = bl.safeValue(json, 'error');
				if (error != null && error != 0) {
					var msg = bl.safeValue(json, "msg", "An unknown error occurred.");
					alert(msg);
				}
				var t_id = bl.safeValue(json, "transactionID");
				if (t_id != '') {
					var tf = bl.ref("transactionID");
					if (tf != null) {
						tf.value(t_id);
					}
				}
				callback(json);
			}
		}
		bl.post(bl.serverEndPoint, data, standardJSONResponseHandler);
	}
	
	params(array, encodeValues) {
		if (typeof encodeValues == 'undefined')
			encodeValues = false;
		var params = [];
		for (let key in array) {
			let value = array[key];
			if (encodeValues)
				value = encodeURIComponent(value);
			params.push(key+"="+value);
		}
		return bl.implode('&', params);
	}
	
	pa(page, action, extraParams) {
		var out = {
			"page" : page, 
			"action" : action
		};
		for (let key in extraParams) {
			if (key == "page" || key == "action")
				continue;
			out[key] = extraParams[key];
		}
		
		return out;
	}
	
	submitForm(url, formID, callback, callbackError) {
		var vals = new Array();
		var form = bl.e(formID);
		var i, j;
		
		var inputs = form.getElementsByTagName("input");
		for (i = 0; i < inputs.length; i++)
		{
			if ((inputs[i].type == "checkbox" && ! inputs[i].checked) || (inputs[i].type == "radio" && ! inputs[i].checked))
				continue;
			vals[i] = inputs[i].name+"="+encodeURIComponent(inputs[i].value);
		}
		var selects = form.getElementsByTagName("select");
		for (j = 0, i++; j < selects.length; j++, i++)
		{
			vals[i] = selects[j].name+"="+encodeURIComponent(selects[j].value);
		}
		var textareas = form.getElementsByTagName("textarea");
		for (j = 0, i++; j < textareas.length; j++, i++)
		{
			vals[i] = textareas[j].name+"="+encodeURIComponent(textareas[j].value);
		}
		this.post(url, vals.join("&"), callback, callbackError);
	}
}

const bl = new blutils();
Object.freeze(bl);


class BLElement {
	
	constructor(element) {
		if (typeof element == 'string')
			element = bl.e(element);
		this.element = element;
	}
	
	hide() {
		bl.hide(this.element);
		return this;
	}
	
	show(style) {
		bl.show(this.element, style);
		return this;
	}
	
	add(type, props) {
		return bl.add(type, this.element, props);
	}
	
	e() {
		return this.element;
	}
	
	style(attribs) {
		if (typeof attribs == 'undefined')
			return this.element.style;
		else {
			this.element.style = attribs;
			return this;
		}
	}
	
	name(value) {
		if (typeof value == 'undefined')
			return this.element.getAttribute('name');
		else {
			this.element.setAttribute('name', value);
			return this;
		}
	}
    
    class(classStr) {
        if (typeof classStr == 'undefined')
            return this.element.className;
        else {
            this.element.className = classStr;
            return this;
        }
    }
	
	addClass(classStr) {
		this.element.className += (" "+classStr);
	}
    
    substitute(type, props) {
        var el = document.createElement(type);
		if (typeof props != 'undefined') {
			for (let n in props)
				el.setAttribute(n, props[n]);
		}
        this.element.parentNode.replaceChild(el, this.element);
        this.element = el;
        return this;
    }
	
	html(contents) {
		if (typeof contents == 'undefined')
			return this.element.innerHTML;
		else {
			this.element.innerHTML = contents;
			return this;
		}
	}
	
	// set the selection for select fields.
	select(value) {
		for (var i = 0; i < this.element.options.length; i++)
		{
			if (this.element.options[i].value == value) {
				this.element.selectedIndex = i;
				break;
			}
		}
		return this;
	}
	
	// get or set value or an element. This function is more most relevance to 
	// form fields. It can be used with standard inputs and selects.
	value(newValue) {
		if (this.element.nodeName == 'SELECT') {
			if (typeof newValue == 'undefined') {
				return this.element.options[this.element.selectedIndex].value;
			}
			this.select(newValue);
		}
        else if (this.element.nodeName == 'TEXTAREA') {
			if (typeof newValue == 'undefined') {
				return this.html();
			}
            this.html(newValue);
            return this;
        }
        else if (this.element.nodeName == 'INPUT' && (this.element.type == 'checkbox' || this.element.type == 'radio'))
        {
			if (typeof newValue == 'undefined') {
				return this.element.checked;
			}
            if (newValue == 1)
                newValue = true;
            else if (newValue == 0)
                newValue = false;
			this.element.checked = newValue;
			return this;
        }
		else {
			if (typeof newValue == 'undefined') {
				return this.element.value;
			}
			this.element.value = newValue;
			return this;
		}
	}
	
	// Remove the element from the DOM and delete the reference object.
	remove() {
		this.element.parentNode.removeChild(this.element);
		delete this;
	}
	
	/* add a bootstrap row and a set of columns. */
	bsrow(noOfColumns, props, colProps) {
		if (typeof props == 'undefined')
			props = [];
		var eclass = bl.safeValue(props, "class", "row");
		props["class"] = eclass;
		var row = this.add("div", props);
		
		var colms = 12 / noOfColumns;
		
		if (typeof colProps == 'undefined') {
			colProps = {"class" : "col-xs-12 col-md-"+colms};
		}
		else if (bl.safeValue(colProps, "class") == "") {
			colProps["class"] = "col-xs-12 col-md-"+colms;
		}
		var cols = [];
		for (let i = 0; i < noOfColumns; i++)
			cols.push(row.add("div", colProps));
		
		return {
			"row" : row,
			"cols" : cols
		};
	}
} 