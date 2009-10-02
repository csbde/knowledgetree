<?php
	require_once("path.php");
	
	class htmlHelper {
	var $tags = array(
		'meta' => '<meta%s/>',
		'metalink' => '<link href="%s"%s/>',
		'link' => '<a href="%s"%s>%s</a>',
		'mailto' => '<a href="mailto:%s" %s>%s</a>',
		'form' => '<form %s>',
		'formend' => '</form>',
		'input' => '<input name="%s" %s/>',
		'textarea' => '<textarea name="%s" %s>%s</textarea>',
		'hidden' => '<input type="hidden" name="%s" %s/>',
		'checkbox' => '<input type="checkbox" name="%s" %s/>',
		'checkboxmultiple' => '<input type="checkbox" name="%s[]"%s />',
		'radio' => '<input type="radio" name="%s" id="%s" %s />%s',
		'selectstart' => '<select name="%s"%s>',
		'selectmultiplestart' => '<select name="%s[]"%s>',
		'selectempty' => '<option value=""%s>&nbsp;</option>',
		'selectoption' => '<option value="%s"%s>%s</option>',
		'selectend' => '</select>',
		'optiongroup' => '<optgroup label="%s"%s>',
		'optiongroupend' => '</optgroup>',
		'checkboxmultiplestart' => '',
		'checkboxmultipleend' => '',
		'password' => '<input type="password" name="%s" %s/>',
		'file' => '<input type="file" name="%s" %s/>',
		'file_no_model' => '<input type="file" name="%s" %s/>',
		'submit' => '<input type="submit" %s/>',
		'submitimage' => '<input type="image" src="%s" %s/>',
		'button' => '<input type="%s" %s/>',
		'image' => '<img src="%s" %s/>',
		'tableheader' => '<th%s>%s</th>',
		'tableheaderrow' => '<tr%s>%s</tr>',
		'tablecell' => '<td%s>%s</td>',
		'tablerow' => '<tr%s>%s</tr>',
		'block' => '<div%s>%s</div>',
		'blockstart' => '<div%s>',
		'blockend' => '</div>',
		'tag' => '<%s%s>%s</%s>',
		'tagstart' => '<%s%s>',
		'tagend' => '</%s>',
		'para' => '<p%s>%s</p>',
		'parastart' => '<p%s>',
		'label' => '<label for="%s"%s>%s</label>',
		'fieldset' => '<fieldset%s>%s</fieldset>',
		'fieldsetstart' => '<fieldset><legend>%s</legend>',
		'fieldsetend' => '</fieldset>',
		'legend' => '<legend>%s</legend>',
		'css' => '<link rel="%s" type="text/css" href="%s" %s/>',
		'style' => '<style type="text/css"%s>%s</style>',
		'charset' => '<meta http-equiv="Content-Type" content="text/html; charset=%s" />',
		'ul' => '<ul%s>%s</ul>',
		'ol' => '<ol%s>%s</ol>',
		'li' => '<li%s>%s</li>',
		'error' => '<div%s>%s</div>'
	);
	
		function __construct() {
		}
		
		function js($name) {
			return "<script type=\"text/javascript\" src=\"resources/js/$name\"></script>";
		}
		
		function css($name) {
			return "<link rel=\"stylesheet\" type=\"text/css\" href=\"resources/css/$name\" />";
		}
		
		function image($name, $options = array()) {
			$path = "resources/graphics/$name";
			$image = sprintf($this->tags['image'], $path, $this->_parseAttributes($options, null, '', ' '));
			
			return $image; 
			//return "<img src=\"resources/graphics/$name\"/>";
		}
		
		function _parseAttributes($options, $exclude = null, $insertBefore = ' ', $insertAfter = null) {
			if (is_array($options)) {
				$options = array_merge(array('escape' => true), $options);
	
				if (!is_array($exclude)) {
					$exclude = array();
				}
				$keys = array_diff(array_keys($options), array_merge((array)$exclude, array('escape')));
				$values = array_intersect_key(array_values($options), $keys);
				$escape = $options['escape'];
				$attributes = array();
	
				foreach ($keys as $index => $key) {
					$attributes[] = $this->__formatAttribute($key, $values[$index], $escape);
				}
				$out = implode(' ', $attributes);
			} else {
				$out = $options;
			}
			return $out ? $insertBefore . $out . $insertAfter : '';
		}
		
	function __formatAttribute($key, $value, $escape = true) {
		$attribute = '';
		$attributeFormat = '%s="%s"';
		$minimizedAttributes = array('compact', 'checked', 'declare', 'readonly', 'disabled', 'selected', 'defer', 'ismap', 'nohref', 'noshade', 'nowrap', 'multiple', 'noresize');
		if (is_array($value)) {
			$value = '';
		}

		if (in_array($key, $minimizedAttributes)) {
			if ($value === 1 || $value === true || $value === 'true' || $value == $key) {
				$attribute = sprintf($attributeFormat, $key, $key);
			}
		} else {
			$attribute = sprintf($attributeFormat, $key, $this->ife($escape, $this->h($value), $value));
		}
		return $attribute;
	}
	
	function ife($condition, $val1 = null, $val2 = null) {
		if (!empty($condition)) {
			return $val1;
		}
		return $val2;
	}
	
	function h($text, $charset = 'UTF-8') {
		if (is_array($text)) {
			return array_map('h', $text);
		}
		return htmlspecialchars($text, ENT_QUOTES, $charset);
	}
	}
?>