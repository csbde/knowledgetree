<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 */

require_once(KT_LIB_DIR . "/validation/basevalidator.inc.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");


class KTStringValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.string';

    var $iMinLength;
    var $iMaxLength;
    var $sMinLengthWarning;
    var $sMaxLengthWarning;



    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        $this->iMinLength = KTUtil::arrayGet($aOptions, 'min_length', 0);
        $this->iMaxLength = KTUtil::arrayGet($aOptions, 'max_length', 254);        // sane default for char fields...

        $this->sMinLengthWarning = KTUtil::arrayGet($aOptions, 'min_length_warning',
            sprintf(_kt('You must provide a value which is at least %d characters long.'), $this->iMinLength));
        $this->sMaxLengthWarning = KTUtil::arrayGet($aOptions, 'max_length_warning',
            sprintf(_kt('You must provide a value which is at most %d characters long.'), $this->iMaxLength));

        $this->bTrim = KTUtil::arrayGet($aOptions, 'trim', true, false);
    }

    function validate($data) {
        $results = array();
        $errors = array();

        // very simple if we're required and not present, fail
        // otherwise, its ok.
        $val = KTUtil::arrayGet($data, $this->sInputVariable);

        if ($this->bTrim) {
            $val = trim($val);
        }

        $l = KTUtil::utf8_strlen($val);
        if ($l < $this->iMinLength) {
            $errors[$this->sBasename] = $this->sMinLengthWarning;
        } else if ($l > $this->iMaxLength) {
            $errors[$this->sBasename] = $this->sMaxLengthWarning;
        }

        if ($this->bProduceOutput) {
            $results[$this->sOutputVariable] = $val;
        }

        return array(
            'errors' => $errors,
            'results' => $results,
        );
    }
}

class KTIllegalCharValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.illegal_char';
    var $sWarning;

    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        $sChars =  "\/*<>|%+':\"?";
        $sWarning = sprintf(_kt('The value you have entered is invalid. The following characters are not allowed: %s'), $sChars);
        $this->sWarning = KTUtil::arrayGet($aOptions, 'illegal_character_warning', $sWarning);

        $this->bTrim = KTUtil::arrayGet($aOptions, 'trim', true, false);
    }

    function validate($data) {
        $results = array();
        $errors = array();

        // very simple if we're required and not present, fail
        // otherwise, its ok.
        $val = KTUtil::arrayGet($data, $this->sInputVariable);

        if ($this->bTrim) {
            $val = trim($val);
        }

        // illegal characters: \/ *<>|%+':"?
        $pattern = "[\*|\%|\\\|\/|\<|\>|\+|\:|\?|\||\'|\"]";
        // "'^[^:]+:(?:[0-9a-z\.\?&-_=\+\/]+[\.]{1})*(?:[0-9a-z\.\?&-_=\+\/]+\.)[a-z]{2,3}.*$'i"
        if(preg_match($pattern, $val)){
            $errors[$this->sBasename] = $this->sWarning;
        }

        if ($this->bProduceOutput) {
            $results[$this->sOutputVariable] = $val;
        }

        return array(
            'errors' => $errors,
            'results' => $results,
        );
    }
}

class KTEntityValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.entity';

    var $sEntityClass;
    var $sGetFunction;

    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        $this->sEntityClass = KTUtil::arrayGet($aOptions, 'class');
        if (empty($this->sEntityClass)) {
            return PEAR::raiseError(_kt("No entity class specified."));
        }
        $this->sGetFunction = KTUtil::arrayGet($aOptions, 'id_method', 'get');
        $this->bMultiple = KTUtil::arrayGet($aOptions, 'multi', false, false);
    }

    function validate($data) {
        $results = array();
        $errors = array();



        $aFunc = array($this->sEntityClass, $this->sGetFunction);


        $val = KTUtil::arrayGet($data, $this->sInputVariable);
        $output = null;
        if (!empty($val)) {
            if ($this->bMultiple) {
                // we probably have an array, but make sure
                $val = (array) $val;

                $failed = array();
                foreach ($val as $id) {
                    $oEntity =& call_user_func($aFunc, $id);
                    if (PEAR::isError($oEntity)) {
                        $failed[] = $id;
                    } else {
                        if ($this->aOptions['ids']) {
                            $output[] = $id;
                        } else {
                            $output[] =& $oEntity;
                        }
                    }
                }
                if (!empty($failed)) {
                    $errors[$this->sBasename] = sprintf(_kt("No such id's: %s"), implode(', ', $failed));
                }
            } else {
                $oEntity =& call_user_func($aFunc, $val);
                if (PEAR::isError($oEntity)) {
                    $errors[$this->sBasename] = sprintf(_kt("No such id: %s"), $val);
                }
                if ($this->aOptions['ids']) {
                    $output = $val;
                } else {
                    $output =& $oEntity;
                }
            }
        }

        if ($this->bProduceOutput) {
            $results[$this->sOutputVariable] = $output;
        }

        return array(
            'errors' => $errors,
            'results' => $results,
        );
    }
}

// the required validator checks either single or multiple items
// in the data array.
class KTRequiredValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.required';

    function validate($data) {
        $errors = array();

        $val = KTUtil::arrayGet($data, $this->sInputVariable);
        if (empty($val)) {
            $errors[$this->sBasename] = _kt("You must provide a value for this field.");
        }

        return array(
            'errors' => $errors,
            'results' => array(),
        );
    }
}

// the required validator checks either single or multiple items
// in the data array.
class KTRequiredFileValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.requiredfile';

    function validate($data) {
        $errors = array();

        $val = KTUtil::arrayGet($_FILES, $this->sInputVariable);
        if (empty($val) || empty($val['name'])) {
            $errors[$this->sBasename] = _kt("You must select a file to upload.");
        }

        return array(
            'errors' => $errors,
            'results' => array(),
        );
    }
}

class KTEmailValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.emailaddress';

    function validate($data) {
        $results = array();
        $errors = array();

        // very simple if we're required and not present, fail
        // otherwise, its ok.
        $val = KTUtil::arrayGet($data, $this->sInputVariable);

        $sEmailAddress = trim($val);

        if (!ereg ("^[^@ ]+@[^@ ]+\.[^@ \.]+$", $sEmailAddress )) {
            $errors[$this->sBasename] = KTUtil::arrayGet($this->aOptions,
                'message',
                _kt("This is not a valid email address."));
        }

        if ($this->bProduceOutput) {
            $results[$this->sOutputVariable] = $sEmailAddress;
        }

        return array(
            'errors' => $errors,
            'results' => $results,
        );
    }
}


class KTBooleanValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.boolean';

    function validate($data) {
        $results = array();
        $errors = array();

        // very simple if we're required and not present, fail
        // otherwise, its ok.
        $val = KTUtil::arrayGet($data, $this->sInputVariable);

        $out = ($val == true);

        if ($this->bProduceOutput) {
            $results[$this->sOutputVariable] = $out;
        }

        return array(
            'errors' => $errors,
            'results' => $results,
        );
    }
}


class KTPasswordValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.password';

    function validate($data) {
        $results = array();
        $errors = array();

        $bundle = KTUtil::arrayGet($data, '_password_confirm_' . $this->sBasename);
        if ($bundle['base'] != $bundle['confirm']) {
            $errors[$this->sBasename] = _kt('Your passwords do not match.');
        }

        if ($this->bProduceOutput) {
            $results[$this->sOutputVariable] = $val;
        }

        return array(
            'errors' => $errors,
            'results' => $results,
        );
    }
}

class KTMembershipValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.membership';

    var $bMulti;
    var $aVocab;

    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        $this->bMulti = KTUtil::arrayGet($aOptions, 'multi', false);
        $vocab = (array) KTUtil::arrayGet($aOptions, 'vocab');
        $this->aVocab = array();
        foreach ($vocab as $v) {
            $this->aVocab[$v] = true;
        }
    }

    function validate($data) {
        $results = array();
        $errors = array();

        // very simple if we're required and not present, fail
        // otherwise, its ok.
        $val = KTUtil::arrayGet($data, $this->sInputVariable);
        if (empty($val)) {
            ; // pass
        } else {
            if ($this->bMulti) {
                $val = (array) $val;
                $failed = array();
                foreach ($val as $k) {
                    if (!$this->aVocab[$k]) {
                        $failed[] = $k;
                    }
                }
                if (!empty($failed)) {
                    $errors[$this->sBasename] = KTUtil::arrayGet($this->aOptions,
                        'error_message', sprintf(_kt('"%s" are not valid selections.'),
                            implode(', ', $failed)));
                }
            } else {

                $mandatory=true;

				if (substr($this->sInputVariable, 0, 9) == 'metadata_')
				{
					$fieldid = substr($this->sInputVariable, 9);
					$field = DocumentField::get($fieldid);
					$mandatory = $field->getIsMandatory();
				}

                if (!array_key_exists($val, $this->aVocab) && $mandatory) {
                    $errors[$this->sBasename] = KTUtil::arrayGet($this->aOptions,
                        'error_message', sprintf(_kt('"%s"is not a valid selection.'), $val));
                }
            }
        }

        if ($this->bProduceOutput) {
            $results[$this->sOutputVariable] = $val;
        }

        return array(
            'errors' => $errors,
            'results' => $results,
        );
    }
}


class KTFieldsetValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.fieldset';

    var $_validators;

    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        $this->_validators = (array) KTUtil::arrayGet($aOptions, 'validators', array());
    }

    function validate($data) {
        $results = array();
        $errors = array();

        // very simple if we're required and not present, fail
        // otherwise, its ok.
        $d = (array) KTUtil::arrayGet($data, $this->sInputVariable);
        //var_dump($this); exit(0);
        foreach ($this->_validators as $v) {
            if(is_null($v)){
                continue;
            }
            $res = $v->validate($d);

            // results comes out with a set of names and values.
            // these *shouldn't* overlap, so just merge them
            $extra_results = KTUtil::arrayGet($res, 'results', array());
            $results = kt_array_merge($results, $extra_results);

            // errors *can* overlap
            // the format is:
            //   basename => array(errors)
            // so that a given field can have multiple errors
            // from multiple validators
            //
            // there is also a "global" error notice stored against the var
            // _kt_global
            $extra_errors = KTUtil::arrayGet($res, 'errors', array());
            foreach ($extra_errors as $varname => $aErrors) {
                if (is_string($aErrors)) {
                    $errors[$varname][] = $aErrors;
                } else {
                    $errors[$varname] = kt_array_merge($errors[$varname], $aErrors);
                }
            }
        }
        $final_results = array();
        if ($this->bProduceOutput) {
            $final_results[$this->sOutputVariable] = $results;
        }

        $final_errors = array();
        if (!empty($errors)) {
            $final_errors[$this->sInputVariable] = $errors;
        }

        return array(
            'errors' => $final_errors,
            'results' => $final_results,
        );
    }
}


class KTFileValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.file';
    // we don't actual need to do *anything*

    function validate($data) {
        $d = (array) KTUtil::arrayGet($data, $this->sInputVariable);
        $results = array();
        if ($this->bProduceOutput) {
            $results[$this->sOutputVariable] = $d;
        }
        return array(
            'errors' => array(),
            'results' => $results,
        );
    }
}

class KTFileIllegalCharValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.fileillegalchar';
    var $sWarning;

    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        $sChars =  "\/*<>|%+':\"?";
        $sWarning = sprintf(_kt('The name of the document selected is invalid. The following characters are not allowed: %s'), $sChars);
        $this->sWarning = KTUtil::arrayGet($aOptions, 'file_illegal_character_warning', $sWarning);

        $this->bTrim = KTUtil::arrayGet($aOptions, 'trim', true, false);
    }

    function validate($data) {
        $results = array();
        $errors = array();

        $aFile = (array) KTUtil::arrayGet($data, $this->sInputVariable);

        // Get the file name
        $val = $aFile['name'];
        if ($this->bTrim) {
            $val = trim($val);
        }

        // illegal characters: \/ *<>|%+':"?
        $pattern = "[\*|\%|\\\|\/|\<|\>|\+|\:|\?|\||\'|\"]";
        if(preg_match($pattern, $val)){
            $errors[$this->sBasename] = $this->sWarning;
        }

        if ($this->bProduceOutput) {
            $results[$this->sOutputVariable] = $aFile;
        }

        return array(
            'errors' => $errors,
            'results' => $results,
        );
    }
}


class KTArrayValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.array';

    function validate($data) {
        $results = array();
        $errors = array();

        // very simple if we're required and not present, fail
        // otherwise, its ok.
        $val = KTUtil::arrayGet($data, $this->sInputVariable);
        //var_dump($data); exit(0);
        if ($this->bProduceOutput) {
            $results[$this->sOutputVariable] = $val;
        }

        return array(
            'errors' => $errors,
            'results' => $results,
        );
    }
}

class KTDateValidator extends KTValidator {
    var $sNamespace = 'ktcore.validators.date';

    var $sFormat;
    var $sFormatWarning;

    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        $this->sFormat = KTUtil::arrayGet($aOptions, 'format', 'YYYY-MM-DD');

        $this->sFormatWarning = KTUtil::arrayGet($aOptions, 'format_warning',
            sprintf(_kt('The date entered must be in the format "%s".'), $this->sFormat));

        $this->bTrim = KTUtil::arrayGet($aOptions, 'trim', true, false);
    }

    function validate($data) {
        $results = array();
        $errors = array();
		
        $val = KTUtil::arrayGet($data, $this->sInputVariable);
		
        if ($this->bTrim) {
            $val = trim($val);
        }

		// very simple if we're required and not present, fail
        // otherwise, its ok.
        if (empty($val) || $val == '') {
        		return;
        }
        
        if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $val, $parts))
        {
            //check weather the date is valid of not
            if(checkdate($parts[2],$parts[3],$parts[1])){
            }else{
                $errors[$this->sBasename] = $this->sFormatWarning;
            }
        }else {
            $errors[$this->sBasename] = $this->sFormatWarning;
        }

        if ($this->bProduceOutput) {
            $results[$this->sOutputVariable] = $val;
        }

        return array(
            'errors' => $errors,
            'results' => $results,
        );
    }
}

?>
