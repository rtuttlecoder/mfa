<?php
    // Start session
    session_start();
    ob_start();

    // Load dependencies
    require_once "regswitch.php";
    require_once "DB.php";
    require_once "HTML/Template/IT.php";
    require_once "inc/db.inc";
    require_once "inc/udf.php";
    require('AuthnetAIM.class.php');
	
    // Error reporting
    // ini_set("display_errors", 1);
    error_reporting(E_ALL);

    // Load database
    $dsn = "mysql://{$username}:{$password}@{$hostname}/{$dbname}";
    $conn = DB::connect($dsn);

    // Load template module
    $template = new HTML_Template_IT("./templates/");
    $template->loadTemplatefile("payments.html", true, true);
    $template->setCurrentBlock("MAIN");

    // Define template variables
    $fields = "amount,invoicenum,cardnumber,cardexpmn,cardexpyr,cardcode,firstname,lastname,organization,street1,street2,city,state,postalcode,country,phone,email,vw";
    $fields = explode(",", $fields);

    // Define form variables
    foreach ($fields as $field) {
        if (! isset($_POST[$field])) {
            $_POST[$field] = "";
        }
        if (! isset($_REQUEST[$field])) {
            $_REQUEST[$field] = "";
        }
    }

    // Define misc variables
    $errs = array();
    $transactionId = "";

    // Define states array
    $states = array(
        "AL" => "Alabama",
        "AK" => "Alaska",
        "AZ" => "Arizona",
        "AR" => "Arkansas",
        "CA" => "California",
        "CO" => "Colorado",
        "CT" => "Connecticut",
        "DE" => "Delaware",
        "DC" => "District Of Columbia",
        "FL" => "Florida",
        "GA" => "Georgia",
        "HI" => "Hawaii",
        "ID" => "Idaho",
        "IL" => "Illinois",
        "IN" => "Indiana",
        "IA" => "Iowa",
        "KS" => "Kansas",
        "KY" => "Kentucky",
        "LA" => "Louisiana",
        "ME" => "Maine",
        "MD" => "Maryland",
        "MA" => "Massachusetts",
        "MI" => "Michigan",
        "MN" => "Minnesota",
        "MS" => "Mississippi",
        "MO" => "Missouri",
        "MT" => "Montana",
        "NE" => "Nebraska",
        "NV" => "Nevada",
        "NH" => "New Hampshire",
        "NJ" => "New Jersey",
        "NM" => "New Mexico",
        "NY" => "New York",
        "NC" => "North Carolina",
        "ND" => "North Dakota",
        "OH" => "Ohio",
        "OK" => "Oklahoma",
        "OR" => "Oregon",
        "PA" => "Pennsylvania",
        "RI" => "Rhode Island",
        "SC" => "South Carolina",
        "SD" => "South Dakota",
        "TN" => "Tennessee",
        "TX" => "Texas",
        "UT" => "Utah",
        "VT" => "Vermont",
        "VA" => "Virginia",
        "WA" => "Washington",
        "WV" => "West Virginia",
        "WI" => "Wisconsin",
        "WY" => "Wyoming"
    );

    // Define countries array
    $countries = array(
        "US" => "United States",
        "AF" => "Afghanistan",
        "AX" => "Aland Islands",
        "AL" => "Albania",
        "DZ" => "Algeria",
        "AS" => "American Samoa",
        "AD" => "Andorra",
        "AO" => "Angola",
        "AI" => "Anguilla",
        "AQ" => "Antarctica",
        "AG" => "Antigua and Barbuda",
        "AR" => "Argentina",
        "AM" => "Armenia",
        "AW" => "Aruba",
        "AU" => "Australia",
        "AT" => "Austria",
        "AZ" => "Azerbaijan",
        "BS" => "Bahamas",
        "BH" => "Bahrain",
        "BD" => "Bangladesh",
        "BB" => "Barbados",
        "BY" => "Belarus",
        "BE" => "Belgium",
        "BZ" => "Belize",
        "BJ" => "Benin",
        "BM" => "Bermuda",
        "BT" => "Bhutan",
        "BO" => "Bolivia",
        "BA" => "Bosnia and Herzegovina",
        "BW" => "Botswana",
        "BV" => "Bouvet Island",
        "BR" => "Brazil",
        "IO" => "British Indian Ocean Territory",
        "VG" => "British Virgin Islands",
        "BN" => "Brunei",
        "BG" => "Bulgaria",
        "BF" => "Burkina Faso",
        "BI" => "Burundi",
        "KH" => "Cambodia",
        "CM" => "Cameroon",
        "CA" => "Canada",
        "CV" => "Cape Verde",
        "KY" => "Cayman Islands",
        "CF" => "Central African Republic",
        "TD" => "Chad",
        "CL" => "Chile",
        "CN" => "China",
        "CX" => "Christmas Island",
        "CC" => "Cocos Islands",
        "CO" => "Colombia",
        "KM" => "Comoros",
        "CG" => "Congo",
        "CK" => "Cook Islands",
        "CR" => "Costa Rica",
        "HR" => "Croatia",
        "CU" => "Cuba",
        "CY" => "Cyprus",
        "CZ" => "Czech Republic",
        "CI" => "Côte d'Ivoire",
        "DK" => "Denmark",
        "DJ" => "Djibouti",
        "DM" => "Dominica",
        "DO" => "Dominican Republic",
        "EC" => "Ecuador",
        "EG" => "Egypt",
        "SV" => "El Salvador",
        "GQ" => "Equatorial Guinea",
        "ER" => "Eritrea",
        "EE" => "Estonia",
        "ET" => "Ethiopia",
        "FK" => "Falkland Islands",
        "FO" => "Faroe Islands",
        "FJ" => "Fiji",
        "FI" => "Finland",
        "FR" => "France",
        "GF" => "French Guiana",
        "PF" => "French Polynesia",
        "TF" => "French Southern Territories",
        "GA" => "Gabon",
        "GM" => "Gambia",
        "GE" => "Georgia",
        "DE" => "Germany",
        "GH" => "Ghana",
        "GI" => "Gibraltar",
        "GR" => "Greece",
        "GL" => "Greenland",
        "GD" => "Grenada",
        "GP" => "Guadeloupe",
        "GU" => "Guam",
        "GT" => "Guatemala",
        "GN" => "Guinea",
        "GW" => "Guinea-Bissau",
        "GY" => "Guyana",
        "HT" => "Haiti",
        "HM" => "Heard Island And McDonald Islands",
        "HN" => "Honduras",
        "HK" => "Hong Kong",
        "HU" => "Hungary",
        "IS" => "Iceland",
        "IN" => "India",
        "ID" => "Indonesia",
        "IR" => "Iran",
        "IQ" => "Iraq",
        "IE" => "Ireland",
        "IL" => "Israel",
        "IT" => "Italy",
        "JM" => "Jamaica",
        "JP" => "Japan",
        "JO" => "Jordan",
        "KZ" => "Kazakhstan",
        "KE" => "Kenya",
        "KI" => "Kiribati",
        "KW" => "Kuwait",
        "KG" => "Kyrgyzstan",
        "LA" => "Laos",
        "LV" => "Latvia",
        "LB" => "Lebanon",
        "LS" => "Lesotho",
        "LR" => "Liberia",
        "LY" => "Libya",
        "LI" => "Liechtenstein",
        "LT" => "Lithuania",
        "LU" => "Luxembourg",
        "MO" => "Macao",
        "MK" => "Macedonia",
        "MG" => "Madagascar",
        "MW" => "Malawi",
        "MY" => "Malaysia",
        "MV" => "Maldives",
        "ML" => "Mali",
        "MT" => "Malta",
        "MH" => "Marshall Islands",
        "MQ" => "Martinique",
        "MR" => "Mauritania",
        "MU" => "Mauritius",
        "YT" => "Mayotte",
        "MX" => "Mexico",
        "FM" => "Micronesia",
        "MD" => "Moldova",
        "MC" => "Monaco",
        "MN" => "Mongolia",
        "ME" => "Montenegro",
        "MS" => "Montserrat",
        "MA" => "Morocco",
        "MZ" => "Mozambique",
        "MM" => "Myanmar",
        "NA" => "Namibia",
        "NR" => "Nauru",
        "NP" => "Nepal",
        "NL" => "Netherlands",
        "AN" => "Netherlands Antilles",
        "NC" => "New Caledonia",
        "NZ" => "New Zealand",
        "NI" => "Nicaragua",
        "NE" => "Niger",
        "NG" => "Nigeria",
        "NU" => "Niue",
        "NF" => "Norfolk Island",
        "KP" => "North Korea",
        "MP" => "Northern Mariana Islands",
        "NO" => "Norway",
        "OM" => "Oman",
        "PK" => "Pakistan",
        "PW" => "Palau",
        "PS" => "Palestine",
        "PA" => "Panama",
        "PG" => "Papua New Guinea",
        "PY" => "Paraguay",
        "PE" => "Peru",
        "PH" => "Philippines",
        "PN" => "Pitcairn",
        "PL" => "Poland",
        "PT" => "Portugal",
        "PR" => "Puerto Rico",
        "QA" => "Qatar",
        "RE" => "Reunion",
        "RO" => "Romania",
        "RU" => "Russia",
        "RW" => "Rwanda",
        "SH" => "Saint Helena",
        "KN" => "Saint Kitts And Nevis",
        "LC" => "Saint Lucia",
        "PM" => "Saint Pierre And Miquelon",
        "VC" => "Saint Vincent And The Grenadines",
        "WS" => "Samoa",
        "SM" => "San Marino",
        "ST" => "Sao Tome And Principe",
        "SA" => "Saudi Arabia",
        "SN" => "Senegal",
        "RS" => "Serbia",
        "CS" => "Serbia and Montenegro",
        "SC" => "Seychelles",
        "SL" => "Sierra Leone",
        "SG" => "Singapore",
        "SK" => "Slovakia",
        "SI" => "Slovenia",
        "SB" => "Solomon Islands",
        "SO" => "Somalia",
        "ZA" => "South Africa",
        "GS" => "South Georgia And The South Sandwich Islands",
        "KR" => "South Korea",
        "ES" => "Spain",
        "LK" => "Sri Lanka",
        "SD" => "Sudan",
        "SR" => "Suriname",
        "SJ" => "Svalbard And Jan Mayen",
        "SZ" => "Swaziland",
        "SE" => "Sweden",
        "CH" => "Switzerland",
        "SY" => "Syria",
        "TW" => "Taiwan",
        "TJ" => "Tajikistan",
        "TZ" => "Tanzania",
        "TH" => "Thailand",
        "CD" => "The Democratic Republic Of Congo",
        "TL" => "Timor-Leste",
        "TG" => "Togo",
        "TK" => "Tokelau",
        "TO" => "Tonga",
        "TT" => "Trinidad and Tobago",
        "TN" => "Tunisia",
        "TR" => "Turkey",
        "TM" => "Turkmenistan",
        "TC" => "Turks And Caicos Islands",
        "TV" => "Tuvalu",
        "VI" => "U.S. Virgin Islands",
        "UG" => "Uganda",
        "UA" => "Ukraine",
        "AE" => "United Arab Emirates",
        "GB" => "United Kingdom",
        "UM" => "United States Minor Outlying Islands",
        "UY" => "Uruguay",
        "UZ" => "Uzbekistan",
        "VU" => "Vanuatu",
        "VA" => "Vatican",
        "VE" => "Venezuela",
        "VN" => "Vietnam",
        "WF" => "Wallis And Futuna",
        "EH" => "Western Sahara",
        "YE" => "Yemen",
        "ZM" => "Zambia",
        "ZW" => "Zimbabwe"
    );

    // Form has been submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Test data?
        if ($_POST["firstname"] == "_test") {
            $_POST["firstname"] = "Gertrude (TEST)";
            $_POST["lastname"] = "OMalley (TEST)";
            $_POST["organization"] = "OMalleys (TEST)";
            $_POST["street1"] = "1615 SW Oblivion Ave (TEST)";
            $_POST["city"] = "Houston";
            $_POST["state"] = "TX";
            $_POST["postalcode"] = "77001";
            $_POST["country"] = "US";
            $_POST["phone"] = "503.924.5999";
            $_POST["email"] = "richard@northwind.us";
            $_POST["invoicenum"] = mktime();
            $_POST["cardnumber"] = "4111111111111111";
            $_POST["cardexpmn"] = "12";
            $_POST["cardexpyr"] = "2020";
            $_POST["cardcode"] = "123";
        }

        // Check amount
        if ($_POST["amount"] == "") {
            $errs[] = "Amount is required.";
        } elseif (floatval($_POST["amount"]) < 0) {
            $errs[] = "Amount must be greater than 0.";
        }

        // Check invoicenumber
        if ($_POST["invoicenum"] == "") {
            $errs[] = "Invoice Number is required.";
        }

        // Check firstname
        if ($_POST["firstname"] == "") {
            $errs[] = "First Name is required.";
        } elseif (strlen($_POST["firstname"]) > 50) {
            $errs[] = "First Name is limited to 50 characters.";
        }

        // Check lastname
        if ($_POST["lastname"] == "") {
            $errs[] = "Last Name is required.";
        } elseif (strlen($_POST["lastname"]) > 50) {
            $errs[] = "Last Name is limited to 50 characters.";
        }

        // Check organization
        if ($_POST["organization"] == "") {
            $errs[] = "Company is required.";
        } elseif (strlen($_POST["organization"]) > 100) {
            $errs[] = "Company is limited to 100 characters.";
        }

        // Check street1
        if ($_POST["street1"] == "") {
            $errs[] = "Address required.";
        } elseif (strlen($_POST["street1"]) > 100) {
            $errs[] = "Address is limited to 100 characters.";
        }

        // Check street2
        if (strlen($_POST["street2"]) > 100) {
            $errs[] = "Address (2) is limited to 100 characters.";
        }

        // Check city
        if ($_POST["city"] == "") {
            $errs[] = "City is required.";
        } elseif (strlen($_POST["city"]) > 50) {
            $errs[] = "City is limited to 50 characters.";
        }

        // Check state
        if (($_POST["country"] == "US") && ($_POST["state"] == "")) {
            $errs[] = "State is required.";
        }

        // Check postalcode
        if ($_POST["postalcode"] == "") {
            $errs[] = "Zipcode is required.";
        } elseif (strlen($_POST["postalcode"]) > 15) {
            $errs[] = "Zipcode is limited to 15 characters.";
        }

        // Check country
        if ($_POST["country"] == "") {
            $errs[] = "Country is required.";
        }

        // Check phone
        if ($_POST["phone"] == "") {
            $errs[] = "Phone is required.";
        } elseif (strlen($_POST["phone"]) > 30) {
            $errs[] = "Phone is limited to 30 characters.";
        }

        // Check email
        if ($_POST["email"] == "") {
            $errs[] = "Email is required.";
        } elseif (strlen($_POST["email"]) > 100) {
            $errs[] = "Email is limited to 100 characters.";
        }

        // Check cardnumber
        $tmpNumber = preg_replace("/[^0-9]/", "", $_POST["cardnumber"]);
        if ($_POST["cardnumber"] == "") {
            $errs[] = "Card Number is required.";
        } elseif (! luhnCheck($tmpNumber)) {
            $errs[] = "Card Number is invalid.";
        }

        // Check cardexpiration
        if (($_POST["cardexpmn"] == "") || ($_POST["cardexpyr"] == "")) {
            $errs[] = "Card Expiration is required.";
        }

        // Check cardcode
        if ($_POST["cardcode"] == "") {
            $errs[] = "Card Code is required.";
        }

        if (count($errs) == 0) {
            $_POST["amount"] = floatVal(preg_replace("/[^0-9\.]/", "", $_POST["amount"]));
            $_POST["cardnumber"] = preg_replace("/[^0-9]/", "", $_REQUEST["cardnumber"]);
            $cardex = $_POST["cardexpmn"] . substr($_POST["cardexpyr"], 2, 2);
            try {
            	$payment = new AuthnetAIM('2As2Rb5c', '4nWks5zfH496yK39', false);
            	$payment->setTransaction($_POST["cardnumber"], $cardex, $_POST["amount"], $_POST["cardcode"], $_POST["invoicenum"]);
            	$payment->setParameter("x_first_name", $_POST["firstname"]);
            	$payment->setParameter("x_last_name", $_POST["lastname"]);
            	$payment->setParameter("x_email", $_POST["email"]);
            	$payment->setParameter("x_address", $_POST["street1"]);
            	$payment->setParameter("x_city", $_POST["city"]);
            	$payment->setParameter("x_state", $_POST["state"]);
            	$payment->setParameter("x_country", $_POST["country"]);
            	$payment->setParameter("x_zip", $_POST["postalcode"]);
            	$payment->setParameter("x_phone", $_POST["phone"]);
            	$payment->setParameter("x_description", "invoice #" . $_POST["invoicenum"]);
        		$payment->process();
        		
        		if ($payment->isApproved()) {
        			$transactionId = $payment->getTransactionID();
					// Save to database
					$sql = "insert into payments (Created, Amount, InvoiceNum, CardNumber, CardExpiration, FirstName, LastName, Organization, Street1, Street2, City, State, PostalCode, Country, Phone, Email, TransactionId) values (now(), "
							. $_POST["amount"] . ", "
							. "'" . $_POST["invoicenum"] . "', "
							. "'" . substr($_POST["cardnumber"], strlen($_POST["cardnumber"]) - 5, 4) . "', "
							. "'" . $_POST["cardexpyr"] . "-" . $_POST["cardexpmn"] . "-01', "
							. "'" . $_POST["firstname"] . "', "
							. "'" . $_POST["lastname"] . "', "
							. "'" . $_POST["organization"] . "', "
							. "'" . $_POST["street1"] . "', "
							. "'" . $_POST["street2"] . "', "
							. "'" . $_POST["city"] . "', "
							. "'" . $_POST["state"] . "', "
							. "'" . $_POST["postalcode"] . "', "
							. "'" . $_POST["country"] . "', "
							. "'" . $_POST["phone"] . "', "
							. "'" . $_POST["email"] . "', "
							. "'" . $transactionId . "' "
							. ");";
					$results = $conn->query(trim($sql));
					if (DB::isError($results)) {
						die("Error: " . $results->getMessage() . "\n");
					}

					exit(header("Location: /thanks.php"));
					exit;
        		} else if ($payment->isDeclined()) {
        			$errs[] = "Payment declined - " . $payment->getResponseText();
        		} else if ($payment->isError()) {
        			$error_number = $payment->getResponseSubcode();
        			$error_message = $payment->getResponseText();
        			$errs[] = "ERROR: " . $error_number . "-" . $error_message;
        		} 
            } catch (AuthnetAIMException $e) {
            	echo 'There was an error processing the transaction. Here is the error message: ';
            	echo $e->__toString();
            	exit();
            } 
        } 
     } // end form submitted   

	 // Copy form values to template variables
	 foreach ($fields as $field) {
		 $template->setVariable(strToUpper($field), htmlSpecialChars($_POST[$field]));
	 }

	 // Create template variables for cardexpmn
	 $tmp = '<option value=""></option>';
	 for ($i = 1; $i <= 12; $i++) {
		 $tmp .= '<option value="' . substr("0" . $i, strlen($i) - 1, 2) . '"';
		 if ($_POST["cardexpmn"] == substr("0" . $i, strlen($i) - 1, 2)) {
			 $tmp .= 'selected="selected"';
		 }
		 $tmp .= '>' . substr("0" . $i, strlen($i) - 1, 2) . '</option>';
	 }
	 $template->setVariable("CARDEXPMN", $tmp);

	 // Create template variables for cardexpyr
	 $tmp = '<option value=""></option>';
	 for ($i = date("Y"); $i <= intVal(date("Y")) + 10; $i++) {
		 $tmp .= '<option value="' . $i . '"';
		 if ($_POST["cardexpyr"] == $i) {
			 $tmp .= 'selected="selected"';
		 }
		 $tmp .= '>' . $i . '</option>';
	 }
	 $template->setVariable("CARDEXPYR", $tmp);

	 // Create template variables for state
	 $tmp = '<option value=""></option>';
	 foreach ($states as $abbrev => $state) {
		 $tmp .= '<option value="' . $abbrev . '"';
		 if ($_POST["state"] == $abbrev) {
			 $tmp .= 'selected="selected"';
		 }
		 $tmp .= '>' . $state . '</option>';
	 }
	 $template->setVariable("STATE", $tmp);

	 // Create template variables for country
	 $tmp = '<option value=""></option>';
	 foreach ($countries as $abbrev => $country) {
		 $tmp .= '<option value="' . $abbrev . '"';
		 if ($_POST["country"] == $abbrev) {
			 $tmp .= 'selected="selected"';
		 }
		 $tmp .= '>' . $country . '</option>';
	 }
	 $template->setVariable("COUNTRY", $tmp);

	// Create template variable for errs array
	$tmp = "";
	if (isset($errs)) {
		if (count($errs) > 0) {
			$tmp .= '<div style="color: #ff0000; background: #ffeeee; border: #ff0000 1px dashed; margin: 10px 0; padding: 10px;">';
			$tmp .= '<p style="margin: 0 0 5px 0; padding: 0;">Some errors have occurred while attempting to process the information you provided. Please check your information and try again.</p><ul style="margin: 0; padding: 5px 15px; list-style-type: circle;">';
			foreach ($errs as $err) {
				$tmp .= '<li style="font-size: 12px;">Error: ' . $err . '</li>';
			}
			$tmp = $tmp . '</ul></div>';
		}
		$template->setVariable("ERRS", $tmp);
	}

	 // Parse template and display
	 $template->parseCurrentBlock();
	 $template->show();
