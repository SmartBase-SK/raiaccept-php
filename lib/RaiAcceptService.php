<?php

namespace Raiaccept\RaiacceptApiClient;

class RaiAcceptService
{
    public const STATUS_PENDING = "PENDING";
    public const STATUS_SUCCESS = "SUCCESS";
    public const STATUS_PAID = "PAID";
    public const STATUS_FAILED = "FAILED";
    public const STATUS_CANCELED = "CANCELED";
    public const STATUS_ABANDONED = "ABANDONED";

    /**
     * Transliteration map for non-Latin characters to Latin equivalents
     * Initialized once when class is loaded for optimal performance
     */
    private static array $transliterationMap = [
        // Greek
        'Α'=>'A','α'=>'a','Β'=>'B','β'=>'b','Γ'=>'G','γ'=>'g','Δ'=>'D','δ'=>'d',
        'Ε'=>'E','ε'=>'e','Ζ'=>'Z','ζ'=>'z','Η'=>'E','η'=>'e','Θ'=>'Th','θ'=>'th',
        'Ι'=>'I','ι'=>'i','Κ'=>'K','κ'=>'k','Λ'=>'L','λ'=>'l','Μ'=>'M','μ'=>'m',
        'Ν'=>'N','ν'=>'n','Ξ'=>'X','ξ'=>'x','Ο'=>'O','ο'=>'o','Π'=>'P','π'=>'p',
        'Ρ'=>'R','ρ'=>'r','Σ'=>'S','σ'=>'s','ς'=>'s','Τ'=>'T','τ'=>'t','Υ'=>'Y','υ'=>'y',
        'Φ'=>'Ph','φ'=>'ph','Χ'=>'Ch','χ'=>'ch','Ψ'=>'Ps','ψ'=>'ps','Ω'=>'O','ω'=>'o',

        // Arabic (basic mapping for common names)
        'ا'=>'a','ب'=>'b','ت'=>'t','ث'=>'th','ج'=>'j','ح'=>'h','خ'=>'kh','د'=>'d',
        'ذ'=>'dh','ر'=>'r','ز'=>'z','س'=>'s','ش'=>'sh','ص'=>'s','ض'=>'d','ط'=>'t',
        'ظ'=>'z','ع'=>'a','غ'=>'gh','ف'=>'f','ق'=>'q','ك'=>'k','ل'=>'l','م'=>'m',
        'ن'=>'n','ه'=>'h','و'=>'w','ي'=>'y','ء'=>'','آ'=>'a','أ'=>'a','إ'=>'i',
        'ى'=>'a','ة'=>'h',

        // Hebrew
        'א'=>'','ב'=>'b','ג'=>'g','ד'=>'d','ה'=>'h','ו'=>'v','ז'=>'z','ח'=>'ch',
        'ט'=>'t','י'=>'y','כ'=>'k','ך'=>'k','ל'=>'l','מ'=>'m','ם'=>'m','נ'=>'n',
        'ן'=>'n','ס'=>'s','ע'=>'','פ'=>'p','ף'=>'p','צ'=>'ts','ץ'=>'ts','ק'=>'k',
        'ר'=>'r','ש'=>'sh','ת'=>'t',

        // Cyrillic
        'А'=>'A','а'=>'a','Б'=>'B','б'=>'b','В'=>'V','в'=>'v','Г'=>'G','г'=>'g',
        'Д'=>'D','д'=>'d','Е'=>'E','е'=>'e','Ё'=>'E','ё'=>'e','Ж'=>'Z','ж'=>'z',
        'З'=>'Z','з'=>'z','И'=>'I','и'=>'i','Й'=>'J','й'=>'j','К'=>'K','к'=>'k',
        'Л'=>'L','л'=>'l','М'=>'M','м'=>'m','Н'=>'N','н'=>'n','О'=>'O','о'=>'o',
        'П'=>'P','п'=>'p','Р'=>'R','р'=>'r','С'=>'S','с'=>'s','Т'=>'T','т'=>'t',
        'У'=>'U','у'=>'u','Ф'=>'F','ф'=>'f','Х'=>'H','х'=>'h','Ц'=>'C','ц'=>'c',
        'Ч'=>'Ch','ч'=>'ch','Ш'=>'Sh','ш'=>'sh','Щ'=>'Sch','щ'=>'sch','Ъ'=>'','ъ'=>'',
        'Ы'=>'Y','ы'=>'y','Ь'=>'','ь'=>'','Э'=>'E','э'=>'e','Ю'=>'Yu','ю'=>'yu',
        'Я'=>'Ya','я'=>'ya',

        // Ukrainian
        'Є'=>'Ye','є'=>'ye','І'=>'I','і'=>'i','Ї'=>'Yi','ї'=>'yi','Ґ'=>'G','ґ'=>'g',

        // Belarusian
        'Ў'=>'U','ў'=>'u',

        // Serbian / Macedonian extras
        'Ђ'=>'Dj','ђ'=>'dj','Љ'=>'Lj','љ'=>'lj','Њ'=>'Nj','њ'=>'nj','Ћ'=>'C','ћ'=>'c',
        'Џ'=>'Dz','џ'=>'dz',
    ];

    public static function transliterate(string $string): string
    {
	    $string = self::normalizeUnicodeText($string);

	    return self::sanitizeForFieldFormat($string, OrderFieldFormat::ADDRESS_LINE);
    }

    /**
     * Sanitize and truncate a string for a specific OrderInput field profile.
     */
    public static function sanitize_order_field(string $string, string $format, int $limit = 127): ?string
    {
	    if ($format === OrderFieldFormat::EMAIL) {
		    $string = self::normalizeEmailText($string);
	    } else {
		    $string = self::normalizeUnicodeText($string);
		    $string = self::sanitizeForFieldFormat($string, $format);
	    }

	    $string = self::limitLength($string, $limit);

	    if ($format === OrderFieldFormat::ADDRESS_LINE) {
		    $string = str_replace(array('&', ';', '<', '>', '|', '\\'), ' ', $string);
	    }

	    $string = preg_replace('/\s+/u', ' ', $string) ?? $string;
	    $string = trim($string);

	    return $string === '' ? null : $string;
    }

    /**
     * Check whether a value matches the OrderInput Swagger pattern for the given format.
     */
    public static function matches_order_field_pattern(string $value, string $format): bool
    {
	    if (!isset(OrderFieldFormat::VALIDATION_PATTERNS[$format])) {
		    return true;
	    }

	    return (bool) preg_match(OrderFieldFormat::VALIDATION_PATTERNS[$format], $value);
    }

    public static function transliterate_and_limit_length(string $string, int $limit = 127)
    {
	    // deprecated, use sanitize_order_field() for improved sanitization
	    $string = self::normalizeUnicodeText($string);
	    $string = preg_replace('/[^\p{L}\d\s\'() .,#\/@-]/u', '', $string) ?? $string;
	    $string = self::limitLength($string, $limit);
	    $string = str_replace(array('&', ';', '<', '>', '|', '`', '\\'), ' ', $string);
	    $string = preg_replace('/\s+/u', ' ', $string) ?? $string;
	    $string = trim($string);

	    return $string === '' ? null : $string;
    }

	private static function normalizeUnicodeText(string $string): string
	{
	    if (function_exists('transliterator_transliterate')) {
		    $out = transliterator_transliterate('Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC', $string);
		    $string = is_string($out) ? $out : self::transliterateNonLatinFallback($string);
	    } else {
		    $string = self::transliterateNonLatinFallback($string);
	    }

	    $string = self::stripCombiningMarks($string);
	    $string = preg_replace('/\s+/u', ' ', $string) ?? $string;

	    return trim($string);
	}

	private static function normalizeEmailText(string $string): string
	{
	    $string = trim($string);

	    if (class_exists('Normalizer')) {
		    $normalized = \Normalizer::normalize($string, \Normalizer::FORM_C);
		    if (is_string($normalized)) {
			    return $normalized;
		    }
	    }

	    return $string;
	}

	private static function sanitizeForFieldFormat(string $string, string $format): string
	{
	    switch ($format) {
		    case OrderFieldFormat::PERSON_NAME:
			    return preg_replace("/[^\p{L}\s'.\x60-]/u", '', $string) ?? $string;
		    case OrderFieldFormat::ADDRESS_LINE:
			    return preg_replace("/[^\p{L}\d\s'\x60() .,#\/-]/u", '', $string) ?? $string;
		    case OrderFieldFormat::POSTAL_CODE:
			    return preg_replace('/[^a-zA-Z0-9 -]/', '', $string) ?? $string;
		    case OrderFieldFormat::MERCHANT_REFERENCE:
			    return preg_replace('/[^a-zA-Z0-9_-]/', '', $string) ?? $string;
		    case OrderFieldFormat::FREE_TEXT:
			    return preg_replace('/[\p{C}]/u', '', $string) ?? $string;
		    default:
			    return preg_replace("/[^\p{L}\d\s'\x60() .,#\/-]/u", '', $string) ?? $string;
	    }
	}

	private static function limitLength(string $string, int $limit): string
	{
	    if (function_exists('mb_strimwidth')) {
		    if (mb_strlen($string) > $limit) {
			    return mb_strimwidth($string, 0, $limit);
		    }

		    return $string;
	    }

	    if (strlen($string) > $limit) {
		    return substr($string, 0, $limit);
	    }

	    return $string;
	}

	private static function transliterateNonLatinFallback(string $s): string
	{
		if (!preg_match('/[^\p{Latin}\p{N}\s.\'-]/u', $s)) {
			return $s;
		}

		return strtr($s, self::$transliterationMap);
	}

	private static function stripCombiningMarks(string $string): string
	{
		if (function_exists('transliterator_transliterate')) {
			$out = transliterator_transliterate('NFD; [:Nonspacing Mark:] Remove; NFC', $string);
			if (is_string($out)) {
				return $out;
			}
		}

		if (class_exists('Normalizer')) {
			$decomposed = \Normalizer::normalize($string, \Normalizer::FORM_D);
			if (is_string($decomposed)) {
				$string = preg_replace('/\p{M}/u', '', $decomposed) ?? $decomposed;
				$composed = \Normalizer::normalize($string, \Normalizer::FORM_C);
				if (is_string($composed)) {
					return $composed;
				}
			}
		}

		return preg_replace('/\p{M}/u', '', $string) ?? $string;
	}

    public static function clean_phone_number(string $phone_number): string
    {
	    $phone_number = trim($phone_number);
	    if ($phone_number === '') {
		    return '';
	    }

	    if (strpos($phone_number, '00') === 0) {
		    $phone_number = '+' . substr($phone_number, 2);
	    }

	    if ($phone_number[0] === '+') {
		    $phone_number = '+' . preg_replace('/\D/', '', substr($phone_number, 1));
	    } else {
		    $phone_number = preg_replace('/\D/', '', $phone_number);
	    }

	    if (strlen($phone_number) > 16) {
		    $phone_number = substr($phone_number, 0, 16);
	    }

	    if (!preg_match(OrderFieldFormat::PHONE_VALIDATION_PATTERN, $phone_number)) {
		    return '';
	    }

	    return $phone_number;
    }

    public static function retrieve_access_token_with_credentials($client, $username, $password)
    {
        $apiInstance = new Api\RaiAcceptAPIApi($client);
        try {
            $response = $apiInstance->token($username, $password);
            $response_obj = $response['object'];
            $access_token = $response_obj->getIdToken();
        } catch (\Exception $e) {
            return null;
        }
        return $access_token;
    }

    public static function get_order_transactions($client, string $access_token, string $order_id)
    {
        $apiInstance = new Api\RaiAcceptAPIApi($client);
        try {
            $result = $apiInstance->getOrderTransactions($access_token, $order_id);
        } catch (\Exception $e) {
            return null;
        }

        return $result;
    }

    public static function get_order_details($client, string $access_token, string $order_id)
    {
        $apiInstance = new Api\RaiAcceptAPIApi($client);
        try {
            $result = $apiInstance->getOrderDetails($access_token, $order_id);
        } catch (\Exception $e) {
            return null;
        }

        return $result;
    }

    public static function get_transaction_details($client, string $access_token, string $order_id, string $transaction_id)
    {
        $apiInstance = new Api\RaiAcceptAPIApi($client);
        try {
            $result = $apiInstance->getTransactionDetails($access_token, $order_id, $transaction_id);
        } catch (\Exception $e) {
            return null;
        }

        return $result;
    }

    public static function refund($client, string $access_token, string $order_id, string $transaction_id, $request_obj)
    {
        $apiInstance = new Api\RaiAcceptAPIApi($client);

        return $apiInstance->refund($access_token, $order_id, $transaction_id, $request_obj);
    }

    public static function getPaidStatuses(): array {
        return [
            self::STATUS_PAID,
            self::STATUS_SUCCESS,
        ];
    }

    public static function getRejectedStatuses(): array {
        return [
            self::STATUS_FAILED,
            self::STATUS_CANCELED,
            self::STATUS_ABANDONED,
        ];
    }

    public static function getCancelledStatuses(): array {
        return [
            self::STATUS_CANCELED,
            self::STATUS_ABANDONED,
        ];
    }

    public static function getFailedStatuses(): array {
        return [
            self::STATUS_FAILED,
        ];
    }

    /**
	 * Converts the WooCommerce country codes to 3-letter ISO codes
	 * https://en.wikipedia.org/wiki/ISO_3166-1_alpha-3
	 * @param string WooCommerce's 2 letter country code
	 * @return string ISO 3-letter country code
	 */
	public static function getCountryIso3( $country ) {
	    $countries = array(
            'AF' => 'AFG', //Afghanistan
            'AX' => 'ALA', //&#197;land Islands
            'AL' => 'ALB', //Albania
            'DZ' => 'DZA', //Algeria
            'AS' => 'ASM', //American Samoa
            'AD' => 'AND', //Andorra
            'AO' => 'AGO', //Angola
            'AI' => 'AIA', //Anguilla
            'AQ' => 'ATA', //Antarctica
            'AG' => 'ATG', //Antigua and Barbuda
            'AR' => 'ARG', //Argentina
            'AM' => 'ARM', //Armenia
            'AW' => 'ABW', //Aruba
            'AU' => 'AUS', //Australia
            'AT' => 'AUT', //Austria
            'AZ' => 'AZE', //Azerbaijan
            'BS' => 'BHS', //Bahamas
            'BH' => 'BHR', //Bahrain
            'BD' => 'BGD', //Bangladesh
            'BB' => 'BRB', //Barbados
            'BY' => 'BLR', //Belarus
            'BE' => 'BEL', //Belgium
            'BZ' => 'BLZ', //Belize
            'BJ' => 'BEN', //Benin
            'BM' => 'BMU', //Bermuda
            'BT' => 'BTN', //Bhutan
            'BO' => 'BOL', //Bolivia
            'BQ' => 'BES', //Bonaire, Saint Estatius and Saba
            'BA' => 'BIH', //Bosnia and Herzegovina
            'BW' => 'BWA', //Botswana
            'BV' => 'BVT', //Bouvet Islands
            'BR' => 'BRA', //Brazil
            'IO' => 'IOT', //British Indian Ocean Territory
            'BN' => 'BRN', //Brunei
            'BG' => 'BGR', //Bulgaria
            'BF' => 'BFA', //Burkina Faso
            'BI' => 'BDI', //Burundi
            'KH' => 'KHM', //Cambodia
            'CM' => 'CMR', //Cameroon
            'CA' => 'CAN', //Canada
            'CV' => 'CPV', //Cape Verde
            'KY' => 'CYM', //Cayman Islands
            'CF' => 'CAF', //Central African Republic
            'TD' => 'TCD', //Chad
            'CL' => 'CHL', //Chile
            'CN' => 'CHN', //China
            'CX' => 'CXR', //Christmas Island
            'CC' => 'CCK', //Cocos (Keeling) Islands
            'CO' => 'COL', //Colombia
            'KM' => 'COM', //Comoros
            'CG' => 'COG', //Congo
            'CD' => 'COD', //Congo, Democratic Republic of the
            'CK' => 'COK', //Cook Islands
            'CR' => 'CRI', //Costa Rica
            'CI' => 'CIV', //Côte d\'Ivoire
            'HR' => 'HRV', //Croatia
            'CU' => 'CUB', //Cuba
            'CW' => 'CUW', //Curaçao
            'CY' => 'CYP', //Cyprus
            'CZ' => 'CZE', //Czech Republic
            'DK' => 'DNK', //Denmark
            'DJ' => 'DJI', //Djibouti
            'DM' => 'DMA', //Dominica
            'DO' => 'DOM', //Dominican Republic
            'EC' => 'ECU', //Ecuador
            'EG' => 'EGY', //Egypt
            'SV' => 'SLV', //El Salvador
            'GQ' => 'GNQ', //Equatorial Guinea
            'ER' => 'ERI', //Eritrea
            'EE' => 'EST', //Estonia
            'ET' => 'ETH', //Ethiopia
            'FK' => 'FLK', //Falkland Islands
            'FO' => 'FRO', //Faroe Islands
            'FJ' => 'FIJ', //Fiji
            'FI' => 'FIN', //Finland
            'FR' => 'FRA', //France
            'GF' => 'GUF', //French Guiana
            'PF' => 'PYF', //French Polynesia
            'TF' => 'ATF', //French Southern Territories
            'GA' => 'GAB', //Gabon
            'GM' => 'GMB', //Gambia
            'GE' => 'GEO', //Georgia
            'DE' => 'DEU', //Germany
            'GH' => 'GHA', //Ghana
            'GI' => 'GIB', //Gibraltar
            'GR' => 'GRC', //Greece
            'GL' => 'GRL', //Greenland
            'GD' => 'GRD', //Grenada
            'GP' => 'GLP', //Guadeloupe
            'GU' => 'GUM', //Guam
            'GT' => 'GTM', //Guatemala
            'GG' => 'GGY', //Guernsey
            'GN' => 'GIN', //Guinea
            'GW' => 'GNB', //Guinea-Bissau
            'GY' => 'GUY', //Guyana
            'HT' => 'HTI', //Haiti
            'HM' => 'HMD', //Heard Island and McDonald Islands
            'VA' => 'VAT', //Holy See (Vatican City State)
            'HN' => 'HND', //Honduras
            'HK' => 'HKG', //Hong Kong
            'HU' => 'HUN', //Hungary
            'IS' => 'ISL', //Iceland
            'IN' => 'IND', //India
            'ID' => 'IDN', //Indonesia
            'IR' => 'IRN', //Iran
            'IQ' => 'IRQ', //Iraq
            'IE' => 'IRL', //Republic of Ireland
            'IM' => 'IMN', //Isle of Man
            'IL' => 'ISR', //Israel
            'IT' => 'ITA', //Italy
            'JM' => 'JAM', //Jamaica
            'JP' => 'JPN', //Japan
            'JE' => 'JEY', //Jersey
            'JO' => 'JOR', //Jordan
            'KZ' => 'KAZ', //Kazakhstan
            'KE' => 'KEN', //Kenya
            'KI' => 'KIR', //Kiribati
            'KP' => 'PRK', //Korea, Democratic People\'s Republic of
            'KR' => 'KOR', //Korea, Republic of (South)
            'KW' => 'KWT', //Kuwait
            'KG' => 'KGZ', //Kyrgyzstan
            'LA' => 'LAO', //Laos
            'LV' => 'LVA', //Latvia
            'LB' => 'LBN', //Lebanon
            'LS' => 'LSO', //Lesotho
            'LR' => 'LBR', //Liberia
            'LY' => 'LBY', //Libya
            'LI' => 'LIE', //Liechtenstein
            'LT' => 'LTU', //Lithuania
            'LU' => 'LUX', //Luxembourg
            'MO' => 'MAC', //Macao S.A.R., China
            'MK' => 'MKD', //Macedonia
            'MG' => 'MDG', //Madagascar
            'MW' => 'MWI', //Malawi
            'MY' => 'MYS', //Malaysia
            'MV' => 'MDV', //Maldives
            'ML' => 'MLI', //Mali
            'MT' => 'MLT', //Malta
            'MH' => 'MHL', //Marshall Islands
            'MQ' => 'MTQ', //Martinique
            'MR' => 'MRT', //Mauritania
            'MU' => 'MUS', //Mauritius
            'YT' => 'MYT', //Mayotte
            'MX' => 'MEX', //Mexico
            'FM' => 'FSM', //Micronesia
            'MD' => 'MDA', //Moldova
            'MC' => 'MCO', //Monaco
            'MN' => 'MNG', //Mongolia
            'ME' => 'MNE', //Montenegro
            'MS' => 'MSR', //Montserrat
            'MA' => 'MAR', //Morocco
            'MZ' => 'MOZ', //Mozambique
            'MM' => 'MMR', //Myanmar
            'NA' => 'NAM', //Namibia
            'NR' => 'NRU', //Nauru
            'NP' => 'NPL', //Nepal
            'NL' => 'NLD', //Netherlands
            'AN' => 'ANT', //Netherlands Antilles
            'NC' => 'NCL', //New Caledonia
            'NZ' => 'NZL', //New Zealand
            'NI' => 'NIC', //Nicaragua
            'NE' => 'NER', //Niger
            'NG' => 'NGA', //Nigeria
            'NU' => 'NIU', //Niue
            'NF' => 'NFK', //Norfolk Island
            'MP' => 'MNP', //Northern Mariana Islands
            'NO' => 'NOR', //Norway
            'OM' => 'OMN', //Oman
            'PK' => 'PAK', //Pakistan
            'PW' => 'PLW', //Palau
            'PS' => 'PSE', //Palestinian Territory
            'PA' => 'PAN', //Panama
            'PG' => 'PNG', //Papua New Guinea
            'PY' => 'PRY', //Paraguay
            'PE' => 'PER', //Peru
            'PH' => 'PHL', //Philippines
            'PN' => 'PCN', //Pitcairn
            'PL' => 'POL', //Poland
            'PT' => 'PRT', //Portugal
            'PR' => 'PRI', //Puerto Rico
            'QA' => 'QAT', //Qatar
            'RE' => 'REU', //Reunion
            'RO' => 'ROU', //Romania
            'RU' => 'RUS', //Russia
            'RW' => 'RWA', //Rwanda
            'BL' => 'BLM', //Saint Barth&eacute;lemy
            'SH' => 'SHN', //Saint Helena
            'KN' => 'KNA', //Saint Kitts and Nevis
            'LC' => 'LCA', //Saint Lucia
            'MF' => 'MAF', //Saint Martin (French part)
            'SX' => 'SXM', //Sint Maarten / Saint Matin (Dutch part)
            'PM' => 'SPM', //Saint Pierre and Miquelon
            'VC' => 'VCT', //Saint Vincent and the Grenadines
            'WS' => 'WSM', //Samoa
            'SM' => 'SMR', //San Marino
            'ST' => 'STP', //S&atilde;o Tom&eacute; and Pr&iacute;ncipe
            'SA' => 'SAU', //Saudi Arabia
            'SN' => 'SEN', //Senegal
            'RS' => 'SRB', //Serbia
            'SC' => 'SYC', //Seychelles
            'SL' => 'SLE', //Sierra Leone
            'SG' => 'SGP', //Singapore
            'SK' => 'SVK', //Slovakia
            'SI' => 'SVN', //Slovenia
            'SB' => 'SLB', //Solomon Islands
            'SO' => 'SOM', //Somalia
            'ZA' => 'ZAF', //South Africa
            'GS' => 'SGS', //South Georgia/Sandwich Islands
            'SS' => 'SSD', //South Sudan
            'ES' => 'ESP', //Spain
            'LK' => 'LKA', //Sri Lanka
            'SD' => 'SDN', //Sudan
            'SR' => 'SUR', //Suriname
            'SJ' => 'SJM', //Svalbard and Jan Mayen
            'SZ' => 'SWZ', //Swaziland
            'SE' => 'SWE', //Sweden
            'CH' => 'CHE', //Switzerland
            'SY' => 'SYR', //Syria
            'TW' => 'TWN', //Taiwan
            'TJ' => 'TJK', //Tajikistan
            'TZ' => 'TZA', //Tanzania
            'TH' => 'THA', //Thailand
            'TL' => 'TLS', //Timor-Leste
            'TG' => 'TGO', //Togo
            'TK' => 'TKL', //Tokelau
            'TO' => 'TON', //Tonga
            'TT' => 'TTO', //Trinidad and Tobago
            'TN' => 'TUN', //Tunisia
            'TR' => 'TUR', //Turkey
            'TM' => 'TKM', //Turkmenistan
            'TC' => 'TCA', //Turks and Caicos Islands
            'TV' => 'TUV', //Tuvalu
            'UG' => 'UGA', //Uganda
            'UA' => 'UKR', //Ukraine
            'AE' => 'ARE', //United Arab Emirates
            'GB' => 'GBR', //United Kingdom
            'US' => 'USA', //United States
            'UM' => 'UMI', //United States Minor Outlying Islands
            'UY' => 'URY', //Uruguay
            'UZ' => 'UZB', //Uzbekistan
            'VU' => 'VUT', //Vanuatu
            'VE' => 'VEN', //Venezuela
            'VN' => 'VNM', //Vietnam
            'VG' => 'VGB', //Virgin Islands, British
            'VI' => 'VIR', //Virgin Island, U.S.
            'WF' => 'WLF', //Wallis and Futuna
            'EH' => 'ESH', //Western Sahara
            'YE' => 'YEM', //Yemen
            'ZM' => 'ZMB', //Zambia
            'ZW' => 'ZWE', //Zimbabwe
            'XK' => 'XKK', //Kosovo
        );
		$iso_code = isset( $countries[$country] ) ? $countries[$country] : $country;
		return $iso_code;
	}
}
