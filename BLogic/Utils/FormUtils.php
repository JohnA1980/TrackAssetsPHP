<?php
/**
*
* BLogic
* The Business Logic Web Framework
* 
* @package		BLogic
* @subpackage	Utils
* @version		3.0
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

class FormBuilder
{
    protected $controller;
    
    public function __construct($controller)
    {
        $this->controller = $controller;
    }
    
    public function open($attribs = array())
    {
        $method = safeValue($attribs, "method", "get");
        $action = safeValue($attribs, "action");
        $class = safeValue($attribs, "class");
        $id = safeValue($attribs, "id");
        $name = safeValue($attribs, "name", "mainForm");
        $enctype = safeValue($attribs, "enctype");
        if (! $enctype && safeValue($attribs, "images", false)) {
            $enctype = "multipart/form-data";
        }
        $other = safeValue($attribs, "other");
        
        $tags = array();
        if ($method)
            $tags["method"] = $method;
        if ($action)
            $tags["action"] = $action;
        if ($class)
            $tags["class"] = $class;
        if ($id)
            $tags["id"] = $id;
        if ($name)
            $tags["name"] = $name;
        if ($enctype)
            $tags["enctype"] = $enctype;
        if ($other)
            $tags = array_merge($tags, $other);
        
        $this->generic("form", $tags);
        print "\n";
        
		if (safeValue($attribs, "defaults", true)) 
        {
            $pageID = defined("BL_DEFAULT_PAGE_ID") ? BL_DEFAULT_PAGE_ID : "page";
            $this->generic("input", array(
                "type" => "hidden",
                "name" => "page",
                "value" => "",
                "id" => $pageID
            ));
            print "\n";
            
            $actionID = defined("BL_DEFAULT_ACTION_ID") ? BL_DEFAULT_ACTION_ID : "action";
            $this->generic("input", array(
                "type" => "hidden",
                "name" => "action",
                "value" => "",
                "id" => $actionID
            ));
            print "\n";
		}
    }
    
    public function close()
    {
        print "</form>";
    }
    
    public function generic($element, $attribs = array())
    {
        $compiled = BLArrayUtils::implode_assoc("\" ", $attribs, "=\"")."\"";
        print "<$element $compiled>";
    }
    
    public function radio($keypath, $value, $attribs = array())
    {
        if (array_key_exists("value", $attribs))
            $current = safeValue($attribs, "value");
        else 
            $current = $this->controller->formValueForKeyPath($keypath);
        $strict = safeValue($attribs, "strict", false);
        $label = safeValue($attribs, "label");
        $class = safeValue($attribs, "class");
        $id = safeValue($attribs, "id");
        $style = safeValue($attribs, "style"); 
		$other = safeValue($attribs, "other", []);    
        
        addRadioButton($keypath, $value, $current, $strict, $label, $id, $class, $style, $other);
    }
    
    public function checkbox($keypath, $value, $attribs = array())
    {
        if (array_key_exists("value", $attribs))
            $current = safeValue($attribs, "value");
        else 
            $current = $this->controller->formValueForKeyPath($keypath);
        $strict = safeValue($attribs, "strict", false);
        $label = safeValue($attribs, "label");
        $class = safeValue($attribs, "class");
        $id = safeValue($attribs, "id");
        $style = safeValue($attribs, "style");   
		$other = safeValue($attribs, "other", []); 
        
        addCheckbox($keypath, $value, $current, $strict, $label, $id, $class, $style, $other);
    }
    
    public function text($keypath, $attribs = array())
    {
        if (array_key_exists("value", $attribs))
            $current = safeValue($attribs, "value");
        else 
            $current = $this->controller->formValueForKeyPath($keypath);
                    
        $attribs["name"] = $keypath;
        $attribs["id"] = safeValue($attribs, "id", $keypath); // default the id to keypath.
        $attribs["value"] = $current;
        $attribs["type"] = safeValue($attribs, "type", "text");
        
        $label = safeValue($attribs, "label");
        if ($label) {
            unset($attribs["label"]);
            $id = $attribs["id"];
            print "<label for=\"$id\">$label</label>";
        }
                    
        $this->generic("input", $attribs);
    }
    
    public function hidden($keypath, $attribs = array())
    {
        $attribs["type"] = "hidden";
        $this->text($keypath, $attribs);
    }
    
    public function password($keypath, $attribs = array())
    {
        $attribs["type"] = "password";
        $this->text($keypath, $attribs);
    }
    
    public function email($keypath, $attribs = array())
    {
        $attribs["type"] = "email";
        $this->text($keypath, $attribs);
    }
    
    public function number($keypath, $attribs = array())
    {
        $attribs["type"] = "number";
        $this->text($keypath, $attribs);
    }
    
    public function textarea($keypath, $attribs = array())
    {
        if (array_key_exists("value", $attribs))
            $current = safeValue($attribs, "value");
        else
            $current = $this->controller->formValueForKeyPath($keypath);
        
        $attribs["name"] = $keypath;
        $attribs["id"] = safeValue($attribs, "id", $keypath); // default the id to keypath.
        
        $label = safeValue($attribs, "label");
        if ($label) {
            unset($attribs["label"]);
            $id = $attribs["id"];
            print "<label for=\"$id\">$label</label>";
        }
        
        $compiled = BLArrayUtils::implode_assoc("\" ", $attribs, "=\"")."\"";
        
		print "<textarea $compiled>$current</textarea>";
    }
    
    public function submit($label, $actions, $attribs = array())
    {
        $class = safeValue($attribs, "class");
        $id = safeValue($attribs, "id");
        $callback = safeValue($attribs, "callback");
        $disabled = safeValue($attribs, "disabled");
        
        addSubmitButtonWithActions($label, $actions, $class, $id, $callback, $disabled);
    }
    
    public function button($label, $actions, $attribs = array())
    {
        $class = safeValue($attribs, "class");
        $id = safeValue($attribs, "id");
        $callback = safeValue($attribs, "callback");
        $disabled = safeValue($attribs, "disabled");
        
        addInputButtonWithActions($label, $actions, $class, $id, $callback, $disabled);
    }
	
	public function link($label, $actions, $attribs = array())
	{
        $class = safeValue($attribs, "class");
        $id = safeValue($attribs, "id");
        $callback = safeValue($attribs, "callback");
		$imgPath = safeValue($attribs, "imagePath");
		$target = safeValue($attribs, "target");
		$terminate = safeValue($attribs, "terminate", true);
		$formName = safeValue($attribs, "form", "mainForm");
		
		if (! $callback)
			addSubmitLinkWithActions($formName, $actions, $label, $imgPath, $class, $id, $terminate, $target);
		else
			addSubmitLinkWithActionsAndCallback($callback, $actions, $label, $imgPath, $class, $id, $terminate, $target);
	}
    
    public function select($keypath, $options, $attribs = array())
    {
        $attribs["name"] = $keypath;
        $attribs["id"] = safeValue($attribs, "id", $keypath); // default the id to keypath.
        $empty = safeValue($attribs, "empty");
        if ($empty)
            unset($attribs["empty"]);
        
        $label = safeValue($attribs, "label");
        if ($label) {
            unset($attribs["label"]);
            $id = $attribs["id"];
            print "<label for=\"$id\">$label</label>";
        }
		$useKeysAndValues = safeValue($attribs, "useKeysAndValues", false);
        
        $compiled = BLArrayUtils::implode_assoc("\" ", $attribs, "=\"")."\"";
        
        print "<select $compiled>\n";
        
        if (count($options) > 0)
        {
            $keys = array_keys($options);
            $first = $options[$keys[0]];
            if ($first instanceof BLGenericRecord)
            {
                $valueField = safeValue($attribs, "valueKey", "id");
                $labelField = safeValue($attribs, "labelKey", "name");
                $options = toSelectOptions($options, $valueField, $labelField);
            }    
            else if ($keys[0] === 0 && ! $useKeysAndValues) {
                $values = array_values($options);
                $expanded = array();
                foreach ($values as $value) 
                    $expanded[$value] = $value;
                $options = $expanded;
            }
            
            if (array_key_exists("value", $attribs))
                $default = safeValue($attribs, "value");
			else {
				if (BLStringUtils::endsWith($keypath, "[]")) {
					$keypath = substr($keypath, 0, -2);
				}
				$default = $this->controller->formValueForKeyPath($keypath);
			}
            	
            if ($empty)
                constructSelectOption($default, "", $empty); 
            foreach ($options as $key => $label) {
                constructSelectOption($default, $key, $label); 
            }
        }
        
        print "</select>";
    }
}	

/**
* Convert and Entity array to a select input array, with simple value=>display mapping
* @param type $entities - list of entities
* @param type $valField - value field (default is id)
* @param type $textField - display text field (default is title)
* @param type $topOption - (Optional) string for top row indicating no option selected
* @return type - array of value=>display mappings, which can be passed to printSimpleSelect or printEditForm functions
*/
function toSelectOptions($entities, $valField = 'id', $textField = 'title', $topOption = null) 
{
   $options = array();
   if ($topOption) {
       $options[''] = $topOption;
   }
   foreach ($entities as $entity) {
       $options[$entity->field($valField)] = $entity->field($textField);
   }
   return $options;
}

function formActionsToEncryptedString($array)
{
	$newArray = array();
	foreach ($array as $elementID => $value)
		$newArray[] = "document.getElementById('$elementID').value='".doEncrypt($value)."'";
	return implode(";", $newArray);
}

function constructSelectOption($defaultValue, $value, $displayString, $strict = false)
{
    $value=trim($value);
	echo "<option value='$value'";
	// Allow other data about options to be added. e.g. merchantID for filtering
	// in this case, the display string should have the 'displaystring' key
	if (is_array($displayString)) {
		foreach ($displayString as $key => $kval) {
			if ($key != 'displaystring') {
				echo " $key='$kval' ";
			}
		}
		$displayString = $displayString['displaystring'];
	}
    
	if ($defaultValue == $value || (is_array($defaultValue) && in_array($value, $defaultValue, $strict)))
	{
		echo " selected";
	}
	echo ">$displayString</option>\n";
}

function addRadioButton($keypath, $value, $current, $strict = false, $label = "", $id = "", $class = "", $style = "", $other = [])
{
	if (! $strict)
		$value = trim($value);
	$checked = "";
	if (($strict && $value === $current) || (! $strict && $value == $current))
		$checked = " checked";
	
	if (! $id)
		$id = $keypath;
	if ($class)
		$class = " class=\"$class\"";
	if ($style)
		$style = " style=\"$style\"";
	$compiledOther = BLArrayUtils::implode_assoc("\" ", $other, "=\"")."\"";
	
	echo "<input type='radio' name='$keypath' value='$value' id='$id'$class$style$checked$compiledOther>";
	if ($label)
		echo "&nbsp;$label";
}

function addCheckbox($keypath, $value, $current, $strict = false, $label = "", $id = "", $class = "", $style = "", $other = [])
	{
		if (! $strict)
			$value = trim($value);
		$checked = "";
		if (($strict && $value === $current) || (! $strict && $value == $current))
			$checked = " checked";
	
		if (! $id)
			$id = $keypath;
		if ($class)
			$class = " class=\"$class\"";
		if ($style)
			$style = " style=\"$style\"";
		$compiledOther = (count($other) > 0) ? BLArrayUtils::implode_assoc("\" ", $other, "=\"")."\"" : "";
	
		echo "<input type='checkbox' name='$keypath' value='$value' id='$id'$class$style$checked $compiledOther>";
		if ($label)
			echo "&nbsp;$label";
	}

function addSimpleSelect($keypath, $values, $default = "", $fieldClass = "", $multi = false, $disabled = null) 
{
    if (is_object($default) && $default instanceof PLController) {
        $default = $default->formValueForKeyPath($keypath);
    } 
    $multiple = ($multi) ? 'multiple' : '';
    $classStr = $fieldClass ? " class=\"$fieldClass\"" : "";
    print "<select name=\"$keypath\" id=\"$keypath\"$classStr $multiple $disabled>\n";
    foreach ($values as $key => $value) {
        if (is_array($value)) {
            
        } else {
            constructSelectOption($default, $key, $value);
        }
    }
    print "</select>";
}

function addTextField($name, $value = "", $enabled = true, $class = null, $id = null, $style = null, $addHiddenFieldWhenDisabled = false)
{
	if ($enabled)
	{
		echo "<input type=\"text\" name=\"$name\" value=\"$value\"";
		if ($class)
			echo " class=\"$class\"";
		if ($id)
			echo " id=\"$id\"";
		if ($style)
			echo " style=\"$style\"";
		echo ">";
	}
	else
	{
		echo "<span";
		if ($class)
			echo " class=\"$class\"";
		if ($id)
			echo " id=\"$id\"";
		echo ">$value</span>";
		if ($addHiddenFieldWhenDisabled)
		{
			$id = ($id) ? $id : "";
		}
	}
}

function addTextArea($name, $value = "", $enabled = true, $class = null, $id = null)
{
	if ($enabled)
	{
		echo "<textarea name=\"$name\"";
		if ($class)
			echo " class=\"$class\"";
		if ($id)
			echo " id=\"$id\"";
		echo " />";
		echo $value."</textarea>";
	}
	else
	{
		echo "<span";
		if ($class)
			echo " class=\"$class\"";
		if ($id)
			echo " id=\"$id\"";
		echo ">$value</span>";
	}
}

function addSubmitButtonWithActions($label, $actions, $class = null, $id = null, $callBack = null, $disabled = false)
{
	$name = uniqid("blfm");
	$str = "<input type=\"submit\" name=\"$name\" value=\"$label\"";
	if ($class)
		$str .= " class=\"$class\"";
	if ($id)
		$str .= " id=\"$id\"";
	if ($disabled)
		$str .= " disabled";

	$str .= " onclick=\"";
	foreach ($actions as $field => $action)
		$str .= "this.form.elements['$field'].value='".doEncrypt($action)."';";
	if ($callBack)
		$str .= "return $callBack;";
	$str .= "\"";
        
    $str .= ">";
    
	echo $str;
}

function addInputButtonWithActions($label, $actions, $class = null, $id = null, $callBack = null, $disabled = false)
{
	$name = uniqid("blfm");
	$str = "<input type=\"button\" name=\"$name\" value=\"$label\"";
	if ($class)
		$str .= " class=\"$class\"";
	if ($id)
		$str .= " id=\"$id\"";
	if ($disabled)
		$str .= " disabled";

	$str .= " onclick=\"";
	foreach ($actions as $field => $action)
		$str .= "this.form.elements['$field'].value='".doEncrypt($action)."';";
	if ($callBack)
		$str .= "return $callBack;";
	$str .= "\"";
    
	$str .= ">";
    
	echo $str;
}
    
function addAjaxSubmitButton($label, $actions, $class = null, $id = null, $callBack = null, $disabled = false, $callbackFunc = 'reloadPageCallback')
{
	$name = uniqid("blfm");
	$str = "<input type=\"button\" name=\"$name\" value=\"$label\"";
	if ($class)
		$str .= " class=\"$class\"";
	if ($id)
		$str .= " id=\"$id\"";
	if ($disabled)
		$str .= " disabled";

    $str .= ' onclick="';
    foreach ($actions as $field => $action) {
	    $str .= "this.form.elements['$field'].value='".doEncrypt($action)."';";
    }
    
    $str .= "ajaxSubmitForm(location, 'mainForm', reloadPageCallback, ajaxError); ";
    
    if ($callBack) {
        $str .= "return $callBack;"; 
    }
    
    $str .= '">';
    
	echo $str;
}

function addSubmitLinkWithActionsAndCallback($mainAction, $actions, $label, $img = null, $class = null, $id = null, $terminate = true, $target = null)
{
	if (strrpos($mainAction, "(") === false)
		$mainAction = "$mainAction()";
	echo constructSubmitLink($mainAction, $actions, $label, $img, $class, $id, $terminate, $target);
}

function addSubmitLinkWithActions($formName, $actions, $label, $img = null, $class = null, $id = null, $terminate = true, $target = null)
{
	echo constructSubmitLink("document.getElementById('$formName').submit()", $actions, $label, $img, $class, $id, $terminate, $target);
}

// For internal use. You should not need to call this method directly.
function constructSubmitLink($mainAction, $actions, $label, $img = null, $class = null, $id = null, $terminateLink = true, $target = null)
{
	$str = "<a href=\"javascript: $mainAction\"";
	if ($class)
		$str .= " class=\"$class\"";
	if ($id)
		$str .= " id=\"$id\"";
	if ($target)
		$str .= " target=\"$target\"";
    
    $str .= " onclick=\"";
    foreach ($actions as $field => $action)
        $str .= "document.getElementById('$field').value='".doEncrypt($action)."';";

    $str .= "\"";
            
	/*$str .= " onclick=\"";
	foreach ($actions as $field => $action)
		$str .= "$('#$field').val('".doEncrypt($action)."');";*/

	$str .= "\">";
    
	if ($img)
	{
		$str .= "<img src=\"$img\"";
		if ($class)
			$str .= " class=\"$class\"";
		else
			$str .= " border=0";
		if ($id)
			$str .= " id=\"$id"."Image\"";
		$str .= " />";
	}
	if ($img && $label)
		$str .= "&nbsp;";
	if ($label)
		$str .= $label;
	if ($terminateLink)
		$str .= "</a>";
	return $str;
}

function addLinkWithParams($label, $baseURL, $params = null, $class = null, $id = null, $encryptValues = false, $terminate = true)
{
	$str = "<a href=\"$baseURL";
	if (!empty($params))
	{
		$str .= "?";
		$array = array();
		foreach ($params as $key => $value)
		{
			if ($encryptValues)
				$value = doEncrypt($value);
			$array[] = "$key=".urlencode($value);
		}
		$str .= implode("&", $array)."\"";
	} else {
        $str .= '"';
    }
	if ($class)
		$str .= " class=\"$class\"";
	if ($id)
		$str .= " id=\"$id\"";
	$str .= ">";
	if ($label)
		$str .= $label;
	if ($terminate)
		$str .= "</a>";
	echo $str;
}
