<?php
/**
* Registration Step Controller.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
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
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Installer
* @version Version 0.1
*/

class registration extends Step
{
	/**
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $storeInSession = true;

    /**
     * Controller function for determining the position within the step
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string The step position
     */
    public function doStep()
    {
    	$this->temp_variables = array("step_name"=>"registration");
        $this->setFormInfo();
        $this->loadFromSession();
		if(!$this->inStep("registration")) {
			$this->loadFromSession();
			return 'landing';
		}
		if($this->next()) {
		    if($this->doRun())
		        return 'confirm';
	        return 'error';
		} else if($this->previous()) {

		    return 'previous';
		}else if($this->confirm()) {

		    return 'next';
		}

		return 'landing';
    }

    public function loadFromSession() {
    	$reg = $this->getDataFromSession('registration');
        $this->temp_variables['first_name'] = $this->getPostSafe($reg['first_name']);
    	$this->temp_variables['last_name'] = $this->getPostSafe($reg['last_name']);
    	$this->temp_variables['email_address'] = $this->getPostSafe($reg['email_address']);
    	$this->temp_variables['sel_country'] = $this->getPostSafe($reg['sel_country']);
    	$this->temp_variables['sel_industry'] = $this->getPostSafe($reg['sel_industry']);
    	$this->temp_variables['sel_organization_size'] = $this->getPostSafe($reg['sel_organization_size']);
    	$this->temp_variables['installation_guid'] = $this->getPostSafe($reg['installation_guid']);
    }

	/**
	* Safer way to return post data
	*
	* @author KnowledgeTree Team
	* @params SimpleXmlObject $simplexml
	* @access public
	* @return void
	*/
    public function getPostSafe($key) {
    	$value = isset($key) ? $key : "";
    	return $value;
    }

    public function setInSession() {
        $this->temp_variables['first_name'] = $_POST['submitted']['first_name'];
    	$this->temp_variables['last_name'] = $_POST['submitted']['last_name'];
    	$this->temp_variables['email_address'] = $_POST['submitted']['email_address'];
    	$this->temp_variables['sel_country'] = $_POST['submitted']['country'];
    	$this->temp_variables['sel_industry'] = $_POST['submitted']['industry'];
    	$this->temp_variables['sel_organization_size'] = $_POST['submitted']['organization_size'];
    	// System GUID, comes from session or db, not POST
    	$this->temp_variables['installation_guid'] = $this->util->getSystemIdentifier(false);
    }

    /**
     * Execute the step action to register the user. If the user has already registered then the step will return.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return unknown
     */
	public function doRun()
	{
        if(isset($_POST['registered']) && $_POST['registered'] == 'yes'){
            return true;
        }
		$this->setInSession();
    	// Flip
    	$countries = $this->temp_variables['countries'];
    	$fcountries = array_flip($countries);
    	$_POST['submitted']['country'] = $fcountries[$_POST['submitted']['country']];
	    // Post the form using curl
	    $formPost = $_POST;
	    $formPost['submitted']['installation_guid'] = $this->temp_variables['installation_guid'];
	    // TODO set correctly using auto set mechanism
	    $_SESSION['installers']['registration']['installation_guid'] = $this->temp_variables['installation_guid'];
        $this->curlForm($formPost);

        // Prevent the form being reposted.
        $_POST['registered'] = 'yes';
	    return true;
    }
    
    /**
     * Post the form data to the drupal form using curl
     *
     * @author KnowledgeTree Team
     * @access private
     * @param array $data The post data
     */
    private function curlForm($data)
    {
        $url = 'http://www.knowledgetree.com/installerform';
        $data = http_build_query($data);

		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_exec($ch);
		curl_close($ch);
    }

    /**
     * Post the form data to the drupal form using streams
     *
     * @author KnowledgeTree Team
     * @access private
     * @param array $data The post data
     * @param array $optional_headers Optional headers to be sent with the data
     * @return unknown
     */
    private function postForm($data, $optional_headers = null)
    {
        $url = 'http://www.knowledgetree.com/installerform';
        $data = http_build_query($data);

        $params = array(
            'http' => array(
                  'method' => 'POST',
                  'content' => $data
            ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = fopen($url, 'r', false, $ctx);
        if (!$fp) {
            throw new Exception("Problem with $url, $php_errormsg");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            fclose($fp);
            throw new Exception("Problem reading data from $url, $php_errormsg");
        }
        fclose($fp);
        return $response;
    }

    /**
     * Post the form data to the drupal form using file sockets
     *
     * @author KnowledgeTree Team
     * @access private
     * @param array $data The post data
     */
    private function sendToHost($data)
    {
        $host = 'www.knowledgetree.com';
        $method = 'POST';
        $path = '/installerform';
        $data = http_build_query($data, null, '&amp;');

        $method = strtoupper($method);
        $fp = fsockopen($host, 80);
        fputs($fp, "$method $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
        fputs($fp,"Content-type: application/x-www-form- urlencoded\r\n");
//        fputs($fp,"Content-type: multipart/form-data\r\n");
        fputs($fp, "Content-length: " . strlen($data) . "\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);

        while (!feof($fp)) {
            $buf .= fgets($fp,128);
        }
        fclose($fp);
        return $buf;
    }

    /**
     * Set the information for the form dropdowns
     *
     * @author KnowledgeTree Team
     * @access private
     */
    private function setFormInfo()
    {
        $sizes = array(
            'noneselected' => 'Select Organization Size...',
        	'upto10' => '1-10',
        	'upto20' => '11-20',
        	'upto50' => '21-50',
        	'upto100' => '51-100',
        	'upto500' => '101-500',
        	'upto1000' => '501-1000',
        	'more1000' => 'More than 1000'
	   );

        $industries = array(
            'noneselected' => 'Select Industry...',
        	'apparel' => 'Apparel',
        	'banking' => 'Banking',
        	'biotechnology' => 'Biotechnology',
        	'chemicals' => 'Chemicals',
        	'communications' => 'Communications',
        	'construction' => 'Construction',
        	'consulting' => 'Consulting',
        	'education' => 'Education',
        	'electronics' => 'Electronics',
        	'energy' => 'Energy',
        	'engineering' => 'Engineering',
        	'entertainment' => 'Entertainment',
        	'environmental' => 'Environmental',
        	'finance' => 'Finance',
        	'government' => 'Government',
        	'healthcare' => 'Healthcare',
        	'hospitality' => 'Hospitality',
        	'insurance' => 'Insurance',
        	'machinery' => 'Machinery',
        	'manufacturing' => 'Manufacturing',
        	'media' => 'Media',
        	'nonprofit' => 'Non Profit',
        	'recreation' => 'Recreation',
        	'retail' => 'Retail',
        	'shipping' => 'Shipping',
        	'technology' => 'Technology',
        	'telecommunications' => 'Telecommunications',
        	'transportation' => 'Transportation',
        	'utilities' => 'Utilities',
        	'other' => 'Other',
        	'legal' => 'Legal',
        	'software' => 'Software'
    	);

    	$countries = array(
    		'noneselected' => 'Select Country...',
            'US' => 'UNITED STATES',
            'GB' => 'UNITED KINGDOM',
            'DE' => 'GERMANY',
            'FR' => 'FRANCE',
            'IT' => 'ITALY',
            'ES' => 'SPAIN',
            'AU' => 'AUSTRALIA',
            'IN' => 'INDIA',
            'AF' => 'AFGHANISTAN',
            'AX' => '&Aring;LAND ISLANDS',
            'AL' => 'ALBANIA',
            'DZ' => 'ALGERIA',
            'AS' => 'AMERICAN SAMOA',
            'AD' => 'ANDORRA',
            'AO' => 'ANGOLA',
            'AI' => 'ANGUILLA',
            'AQ' => 'ANTARCTICA',
            'AG' => 'ANTIGUA AND BARBUDA',
            'AR' => 'ARGENTINA',
            'AM' => 'ARMENIA',
            'AW' => 'ARUBA',
            'AT' => 'AUSTRIA',
            'AZ' => 'AZERBAIJAN',
            'BS' => 'BAHAMAS',
            'BH' => 'BAHRAIN',
            'BD' => 'BANGLADESH',
            'BB' => 'BARBADOS',
            'BY' => 'BELARUS',
            'BE' => 'BELGIUM',
            'BZ' => 'BELIZE',
            'BJ' => 'BENIN',
            'BM' => 'BERMUDA',
            'BT' => 'BHUTAN',
            'BO' => 'BOLIVIA',
            'BA' => 'BOSNIA AND HERZEGOVINA',
            'BW' => 'BOTSWANA',
            'BV' => 'BOUVET ISLAND',
            'BR' => 'BRAZIL',
            'IO' => 'BRITISH INDIAN OCEAN TERRITORY',
            'BN' => 'BRUNEI DARUSSALAM',
            'BG' => 'BULGARIA',
            'BF' => 'BURKINA FASO',
            'BI' => 'BURUNDI',
            'KH' => 'CAMBODIA',
            'CM' => 'CAMEROON',
            'CA' => 'CANADA',
            'CV' => 'CAPE VERDE',
            'KY' => 'CAYMAN ISLANDS',
            'CF' => 'CENTRAL AFRICAN REPUBLIC',
            'TD' => 'CHAD',
            'CL' => 'CHILE',
            'CN' => 'CHINA',
            'CX' => 'CHRISTMAS ISLAND',
            'CC' => 'COCOS (KEELING) ISLANDS',
            'CO' => 'COLOMBIA',
            'KM' => 'COMOROS',
            'CG' => 'CONGO',
            'CD' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
            'CK' => 'COOK ISLANDS',
            'CR' => 'COSTA RICA',
            'CI' => "C&Ocirc;TE D'IVOIRE",
            'HR' => 'CROATIA',
            'CU' => 'CUBA',
            'CY' => 'CYPRUS',
            'CZ' => 'CZECH REPUBLIC',
            'DK' => 'DENMARK',
            'DJ' => 'DJIBOUTI',
            'DM' => 'DOMINICA',
            'DO' => 'DOMINICAN REPUBLIC',
            'EC' => 'ECUADOR',
            'EG' => 'EGYPT',
            'SV' => 'EL SALVADOR',
            'GQ' => 'EQUATORIAL GUINEA',
            'ER' => 'ERITREA',
            'EE' => 'ESTONIA',
            'ET' => 'ETHIOPIA',
            'FK' => 'FALKLAND ISLANDS (MALVINAS)',
            'FO' => 'FAROE ISLANDS',
            'FJ' => 'FIJI',
            'FI' => 'FINLAND',
            'GF' => 'FRENCH GUIANA',
            'PF' => 'FRENCH POLYNESIA',
            'TF' => 'FRENCH SOUTHERN TERRITORIES',
            'GA' => 'GABON',
            'GM' => 'GAMBIA',
            'GE' => 'GEORGIA',
            'GH' => 'GHANA',
            'GI' => 'GIBRALTAR',
            'GR' => 'GREECE',
            'GL' => 'GREENLAND',
            'GD' => 'GRENADA',
            'GP' => 'GUADELOUPE',
            'GU' => 'GUAM',
            'GT' => 'GUATEMALA',
            'GG' => 'GUERNSEY',
            'GN' => 'GUINEA',
            'GW' => 'GUINEA-BISSAU',
            'GY' => 'GUYANA',
            'HT' => 'HAITI',
            'HM' => 'HEARD ISLAND AND MCDONALD ISLANDS',
            'VA' => 'HOLY SEE (VATICAN CITY STATE)',
            'HN' => 'HONDURAS',
            'HK' => 'HONG KONG',
            'HU' => 'HUNGARY',
            'IS' => 'ICELAND',
            'ID' => 'INDONESIA',
            'IR' => 'IRAN, ISLAMIC REPUBLIC OF',
            'IQ' => 'IRAQ',
            'IE' => 'IRELAND',
            'IM' => 'ISLE OF MAN',
            'IL' => 'ISRAEL',
            'JM' => 'JAMAICA',
            'JE' => 'JERSEY',
            'JO' => 'JORDAN',
            'KZ' => 'KAZAKHSTAN',
            'KE' => 'KENYA',
            'KI' => 'KIRIBATI',
            'KP' => 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF',
            'KR' => 'KOREA, REPUBLIC OF',
            'KW' => 'KUWAIT',
            'KG' => 'KYRGYZSTAN',
            'LA' => 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC',
            'LV' => 'LATVIA',
            'LB' => 'LEBANON',
            'LS' => 'LESOTHO',
            'LR' => 'LIBERIA',
            'LY' => 'LIBYAN ARAB JAMAHIRIYA',
            'LI' => 'LIECHTENSTEIN',
            'LT' => 'LITHUANIA',
            'LU' => 'LUXEMBOURG',
            'MO' => 'MACAO',
            'MK' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
            'MG' => 'MADAGASCAR',
            'MW' => 'MALAWI',
            'MY' => 'MALAYSIA',
            'MV' => 'MALDIVES',
            'ML' => 'MALI',
            'MT' => 'MALTA',
            'MH' => 'MARSHALL ISLANDS',
            'MQ' => 'MARTINIQUE',
            'MR' => 'MAURITANIA',
            'MU' => 'MAURITIUS',
            'YT' => 'MAYOTTE',
            'MX' => 'MEXICO',
            'FM' => 'MICRONESIA, FEDERATED STATES OF',
            'MD' => 'MOLDOVA, REPUBLIC OF',
            'MC' => 'MONACO',
            'MN' => 'MONGOLIA',
            'ME' => 'MONTENEGRO',
            'MS' => 'MONTSERRAT',
            'MA' => 'MOROCCO',
            'MZ' => 'MOZAMBIQUE',
            'MM' => 'MYANMAR',
            'NA' => 'NAMIBIA',
            'NR' => 'NAURU',
            'NP' => 'NEPAL',
            'NL' => 'NETHERLANDS',
            'AN' => 'NETHERLANDS ANTILLES',
            'NC' => 'NEW CALEDONIA',
            'NZ' => 'NEW ZEALAND',
            'NI' => 'NICARAGUA',
            'NE' => 'NIGER',
            'NG' => 'NIGERIA',
            'NU' => 'NIUE',
            'NF' => 'NORFOLK ISLAND',
            'MP' => 'NORTHERN MARIANA ISLANDS',
            'NO' => 'NORWAY',
            'OM' => 'OMAN',
            'PK' => 'PAKISTAN',
            'PW' => 'PALAU',
            'PS' => 'PALESTINIAN TERRITORY, OCCUPIED',
            'PA' => 'PANAMA',
            'PG' => 'PAPUA NEW GUINEA',
            'PY' => 'PARAGUAY',
            'PE' => 'PERU',
            'PH' => 'PHILIPPINES',
            'PN' => 'PITCAIRN',
            'PL' => 'POLAND',
            'PT' => 'PORTUGAL',
            'PR' => 'PUERTO RICO',
            'QA' => 'QATAR',
            'RE' => 'REUNION',
            'RO' => 'ROMANIA',
            'RU' => 'RUSSIAN FEDERATION',
            'RW' => 'RWANDA',
//            TODO: Special Character for the e
            'BL' => 'SAINT BARTHELEMY',
            'SH' => 'SAINT HELENA',
            'KN' => 'SAINT KITTS AND NEVIS',
            'LC' => 'SAINT LUCIA',
            'MF' => 'SAINT MARTIN',
            'PM' => 'SAINT PIERRE AND MIQUELON',
            'VC' => 'SAINT VINCENT AND THE GRENADINES',
            'WS' => 'SAMOA',
            'SM' => 'SAN MARINO',
            'ST' => 'SAO TOME AND PRINCIPE',
            'SA' => 'SAUDI ARABIA',
            'SN' => 'SENEGAL',
            'RS' => 'SERBIA',
            'SC' => 'SEYCHELLES',
            'SL' => 'SIERRA LEONE',
            'SG' => 'SINGAPORE',
            'SK' => 'SLOVAKIA',
            'SI' => 'SLOVENIA',
            'SB' => 'SOLOMON ISLANDS',
            'SO' => 'SOMALIA',
            'ZA' => 'SOUTH AFRICA',
            'GS' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
            'LK' => 'SRI LANKA',
            'SD' => 'SUDAN',
            'SR' => 'SURINAME',
            'SJ' => 'SVALBARD AND JAN MAYEN',
            'SZ' => 'SWAZILAND',
            'SE' => 'SWEDEN',
            'CH' => 'SWITZERLAND',
            'SY' => 'SYRIAN ARAB REPUBLIC',
            'TW' => 'TAIWAN, PROVINCE OF CHINA',
            'TJ' => 'TAJIKISTAN',
            'TZ' => 'TANZANIA, UNITED REPUBLIC OF',
            'TH' => 'THAILAND',
            'TL' => 'TIMOR-LESTE',
            'TG' => 'TOGO',
            'TK' => 'TOKELAU',
            'TO' => 'TONGA',
            'TT' => 'TRINIDAD AND TOBAGO',
            'TN' => 'TUNISIA',
            'TR' => 'TURKEY',
            'TM' => 'TURKMENISTAN',
            'TC' => 'TURKS AND CAICOS ISLANDS',
            'TV' => 'TUVALU',
            'UG' => 'UGANDA',
            'UA' => 'UKRAINE',
            'AE' => 'UNITED ARAB EMIRATES',
            'UM' => 'UNITED STATES MINOR OUTLYING ISLANDS',
            'UY' => 'URUGUAY',
            'UZ' => 'UZBEKISTAN',
            'VU' => 'VANUATU',
            'VE' => 'VENEZUELA',
            'VN' => 'VIET NAM',
            'VG' => 'VIRGIN ISLANDS, BRITISH',
            'VI' => 'VIRGIN ISLANDS, U.S.',
            'WF' => 'WALLIS AND FUTUNA',
            'EH' => 'WESTERN SAHARA',
            'YE' => 'YEMEN',
            'ZM' => 'ZAMBIA',
            'ZW' => 'ZIMBABWE'
        );
        $this->temp_variables['countries'] = $countries;
        $this->temp_variables['industries'] = $industries;
        $this->temp_variables['org_size'] = $sizes;
    }

    /**
     * Return whether or not to store a step information in session
     *
     * @author KnowledgeTree Team
     * @param none
     * @access public
     * @return boolean
     */
    public function storeInSession() {
    	return $this->storeInSession;
    }
}
?>