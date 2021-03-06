<?php
	/**
	* PortalFW - Framework PHP pour la conception d'un portail personnel
	*
	* /core/pfw_locale.php
	*
	* Fonctions pour la gestion de la localisation et la globalisation
	* Traitement des heures, dates et calendrier
	*
	* LICENSE: This source file is subject to version 3.01 of the PHP license
	* that is available through the world-wide-web at the following URI:
	* http://www.php.net/license/3_01.txt.  If you did not receive a copy of
	* the PHP License and are unable to obtain it through the web, please
	* send a note to pantaflex@hotmail.fr so we can mail you a copy immediately.
	*
	* @category   PHP Framework
	* @package    PortalFW
	* @author     Christophe LEMOINE <pantaflex@hotmail.fr>
	* @copyright  2019 Christophe LEMOINE
	* @license    http://www.php.net/license/3_01.txt  PHP License 3.01
	* @version    0.1.0
	* @link       http://www.portalfw.org
	* @since      File available since Release 0.1.0
	* @required	  PHP module: gettext
	* @required	  PHP module: intl
	*/

	
	/**
	* Liste des formats DATE et HEURE disponibles
	*/
	const DATETIME_FORMAT = array(
		'NONE'		=> \IntlDateFormatter::NONE,
		'SHORT'		=> \IntlDateFormatter::SHORT,
		'MEDIUM'	=> \IntlDateFormatter::MEDIUM,
		'LONG'		=> \IntlDateFormatter::LONG,
		'FULL'		=> \IntlDateFormatter::FULL
	);
	
	
	/**
	* Liste des jours
	*/
	const DOW_DAYS = array(
		'SHORT_FORMAT' => array(
			0 => 'Sun',
			1 => 'Mon',
			2 => 'Tue',
			3 => 'Wed',
			4 => 'Thu',
			5 => 'Fri',
			6 => 'Sat'
		),
		'LONG_FORMAT' => array(
			0 => 'Sunday',
			1 => 'Monday',
			2 => 'Tuesday',
			3 => 'Wednesday',
			4 => 'Thursday',
			5 => 'Friday',
			6 => 'Saturday'
		)
	);
	
	
	/**
	 * Convertit le nom d'un jour en langue locale (version longue)
	 *
	 * @param string $day_name	Nom du jour
	 *
	 * @return string Nom du jour traduit
	 */
	function longDayNameToLocalName( $day_name ) {
		return strftime( '%A', strtotime( $day_name ) );
	}
	
	
	/**
	 * Convertit le nom d'un jour en langue locale (version courte)
	 *
	 * @param string $day_name	Nom du jour
	 *
	 * @return string Nom du jour traduit
	 */
	function shortDayNameToLocalName( $day_name ) {
		return strftime( '%a', strtotime( $day_name ) );
	}
	
	
	/**
	 * Convertit le nom d'un mois en langue locale (version longue)
	 *
	 * @param string $month_name	Nom du mois
	 *
	 * @return string Nom du mois traduit
	 */
	function longMonthNameToLocalName( $month_name ) {
		return strftime( '%B', strtotime( $month_name ) );
	}
	
	
	/**
	 * Convertit le nom d'un mois en langue locale (version courte)
	 *
	 * @param string $month_name	Nom du mois
	 *
	 * @return string Nom du mois traduit
	 */
	function shortMonthNameToLocalName( $month_name ) {
		return strftime( '%b', strtotime( $month_name ) );
	}

	
	/**
	 * Retourne la liste des langues disponibles dans votre dossier de localisation
	 * et prises en charge par le syst??me h??bergeant le serveur PHP
	 *
	 * Le dossier de localisation doit contenir des sous-dossiers nomm??s avec les
	 * codes ISO 639-1 qui contiendront les catalogues ('domains') de traduction
	 *
	 * @param string $dir_name	Nom du dossier contenant les langues prises en charge
	 *    au format ISO 639-1
	 *
	 * @return array Liste des langues disponibles
	 */
	function getAvaillableLanguages( $dir_name = 'locales' ) {
		$dir_name = ( defined( 'ABSPATH' ) ? ABSPATH : './' ) . $dir_name . '/';

		$langs = array_map( function( $directory ) {
			return basename( $directory );
		}, glob( $dir_name . '[a-z][a-z]*_[A-Z][A-Z]*', GLOB_ONLYDIR ) );
		
		$installed = \ResourceBundle::getLocales( '' );
		$available = array();
		foreach( $langs as $lang) {
			if( in_array( $lang, $installed ) )
				$available[] = $lang;
		}
		
		return $available;
	}
	
	
	/**
	 * V??rifie si une langue au format ISO 639-1 est disponible
	 *
	 * @param string $language	Code de la langue au format ISO 639-1
	 * @param string $dir_name	Nom du dossier contenant les langues prises en charge
	 *    au format ISO 639-1
	 *
	 * @return boolean true (vrai) si la langue existe sinon false (faux)
	 */
	function languageExists( $language, $dir_name = 'locales' ) {
		return in_array( trim( $language ), getAvaillableLanguages( $dir_name ) );
	}
	
	
	/**
	 * D??finit la langue qui sera utilis??e pour les traductions et toutes les localisations
	 *
	 * @param string $language	Code de la langue au format ISO 639-1
	 * @param string $codeset	Encodage des traductions ( 'UTF8' par defaut )
	 * @param array $domains	Liste des fichiers catalogue de traductions ?? charger
	 * @param string $dir_name	Nom du dossier contenant les langues prises en charge
	 *    au format ISO 639-1
	 */
	function setLanguage( $language, $codeset = 'UTF-8', $domains = array( 'default' ), $dir_name = 'locales' ) {
		if( !languageExists( $language, $dir_name ) )
			return;
		
		$dir_name = ( defined( 'ABSPATH' ) ? ABSPATH : './' ) . $dir_name . '/';
		
		putenv( 'LANG=' . $language . '.' . $codeset );
		putenv( 'LANGUAGE=' . $language . '.' . $codeset );
		setlocale( LC_ALL, $language . $codeset . '@euro' );
		
		for( $i = 0; $i < count( $domains ); $i++ ) {
			bindtextdomain( $domains[ $i ], $dir_name );
			bind_textdomain_codeset( $domains[ $i ], $codeset );
			
			
			if( $i = 0 )
				textdomain( $domains[ $i ] );
		}
	}
	
	
	/**
	 * Retourne la langue courante utilis??e
	 *
	 * @return string Code ISO 639-1 de la langue
	 */
	function getLanguage() {
		return substr(setlocale( LC_ALL, 0 ),0,5);
	}
	
	
	/**
	 * Retourne la langue courante utilis??e sous forme d'un tableau
	 *
	 * @return array Informations compl??te de la langue par defaut (courante)
	 */
	function getLanguageEx() {
		$locale = setlocale( LC_ALL, 0 );
		
		$encoding = '';
		$lang = explode( '.', $locale );
		if( is_array( $lang )
			&& count( $lang ) >= 1) {
		
			if( count( $lang ) >= 2 )
				$encoding = $lang[ 1 ];
				
			$lang = $lang[ 0 ];
			
		}
		
		$lang = explode( '_', $lang );
		$language_short = '';
		$region_short = '';
		if( is_array( $lang )
			&& count( $lang ) >= 1 ) {
		
			if( count( $lang ) >= 2 )
				$region_short = $lang[ 1 ];
		
			$language_short = $lang[ 0 ];
			
		}
		$locale = substr($locale, 0, 5);
		$language = locale_get_display_language( $locale, $language_short );
		$region = locale_get_display_region( $locale, $language_short );
		$variant = locale_get_display_variant( $locale, $language_short );
		
		return array(
			'locale'		=> $locale,
			'language_code'	=> $language_short,
			'language'		=> $language,
			'region'		=> $region,
			'region_code'	=> $region_short,
			'variant'		=> $variant,
			'encoding'		=> $encoding
		);
	}
	
	
	/**
	 * Retourne la liste des zones horaires disponibles et prises en charge
	 * par le syst??me h??bergeant de le serveur PHP.
	 *
	 * La valeur retourn??e est sous forme d'un tableau contenant diverses informations
	 * li??es ?? chaques zones horaires:
	 * 
	 * <code>
	 * <?php
	 *		$list = getAvaillableTimezones();
	 *		echo '<pre>';
	 *		foreach( $list as $tz ) {
	 * 			echo "Zone:\t" . $tz[ 'zone' ] . '<br />';			// nom de la zone horaire
	 * 			echo "Offset:\t" . $tz[ 'offset' ] . '<br />';		// diff??rence de temps par rapport au fuseau horaire
	 * 			echo "GMT:\t" . $tz[ 'diff_from_GMT' ] . '<br />';	// traduction ??crite de la diff??rence de temps
				echo '<hr />';
	 *		}
	 *		echo '</pre>';
	 *	?>
	 *	</code>
	 *
	 * @return array Liste des zones horaires disponibles
	 */
	function getAvaillableTimezones() {
		$zones_array = array();
		$timestamp = time();
		
		$current_timezone = date_default_timezone_get();
		
		foreach( timezone_identifiers_list() as $key => $zone ) {
		
			date_default_timezone_set( $zone );
			
			$zones_array[ $key ][ 'zone' ]			= $zone;
			$zones_array[ $key ][ 'offset' ]		= (int)( (int)date( 'O', $timestamp ) ) / 100;
			$zones_array[ $key ][ 'diff_from_GMT' ]	= 'UTC/GMT ' . date( 'P', $timestamp );
		}
		
		date_default_timezone_set( $current_timezone );
		
		return $zones_array;
	}
	
	
	/**
	 * V??rifie si une zone horaire existe et est disponible
	 *
	 * @param string $timezone	Zone horaire
	 *
	 * @return boolean Vrai (true) si la zone existe, sinon Faux (false)
	 */
	function timezoneExists( $timezone ) {
		return !array_search( $timezone, array_column( getAvaillableTimezones(), 'zone' ) )
			? false
			: true;
	}
	
	
	/**
	 * Retourne les informations num??rique d'une zone horaire
	 *
	 * @param string $timezone	Nom de la zone horaire
	 *
	 * @return array tableau contenant les informations, NULL en cas d'erreur
	 */
	function timezoneconv( $timezone ) {
		$timezones = getAvaillableTimezones();
		
		$key = array_search( $timezone, array_column( $timezones,  'zone' ) );
		if( !$key )
			return null;
			
		return $timezones[ $key ];
	}
	
	
	/**
	 * Retourne la zone horaire align??e sur a latitude et la longitude souhait??e.
	 * Sp??cifier le code ISO 3166-1 du pays acc??l??re la recherche
	 *
	 * @param float $cur_lat 		Latitude
	 * @param float $cur_long		Longitude
	 * @param float $country_code	Code ISO 3166-1 du pays
	 *
	 * @return string Nom de la zone horaire
	 */
	function getNearestTimezone( $cur_lat, $cur_long, $country_code = '' ) {
		$timezone_ids = ( $country_code ) 
			? \DateTimeZone::listIdentifiers( \DateTimeZone::PER_COUNTRY, strtoupper( $country_code ) )
			: \DateTimeZone::listIdentifiers();

		if( $timezone_ids && is_array( $timezone_ids ) && isset( $timezone_ids[ 0 ] ) ) {

			$time_zone = '';
			$tz_distance = 0;

			if ( count( $timezone_ids ) == 1 ) {
			
				$time_zone = $timezone_ids[ 0 ];
				
			} else {

				foreach( $timezone_ids as $timezone_id ) {
				
					$timezone = new \DateTimeZone( $timezone_id );
					$location = $timezone->getLocation();
					$tz_lat   = $location[ 'latitude' ];
					$tz_long  = $location[ 'longitude' ];

					$theta    = $cur_long - $tz_long;
					
					$distance = ( sin( deg2rad( $cur_lat ) ) * sin( deg2rad( $tz_lat ) ) ) 
					+ ( cos( deg2rad( $cur_lat ) ) * cos( deg2rad( $tz_lat ) ) * cos( deg2rad( $theta ) ) );
					$distance = acos( $distance );
					$distance = abs( rad2deg( $distance ) );

					if ( !$time_zone || $tz_distance > $distance ) {
						$time_zone   = $timezone_id;
						$tz_distance = $distance;
					} 

				}
			}
			
			return  $time_zone;
		}
		
		return 'UTC';
	}
	
	
	/**
	 * D??finit la zone horaire par defaut
	 *
	 * @param string $timezone	Nom de la zone horaire
	 *
	 * @return void
	 */
	function setTimezone( $timezone ) {
		if( !timezoneExists( $timezone ) )
			return;
			
		date_default_timezone_set( $timezone );
	}
	
	
	/**
	 * Retourne la zone horaire par defaut (courante)
	 *
	 * @return string Nom de la zone horaire
	 */
	function getTimezone() {
		return date_default_timezone_get();
	}
	
	
	/**
	 * Convertit un timestamp unix en timestamp d??cal?? en fonction de la zone horaire souhait??e
	 *
	 * @param integer $utc_timestamp	Timestamp unix ?? convertir, NULL par defaut (timestamp actuel)
	 * @param string $to_timezone		Nom de la zone horaire, NULL par defaut (zone horaire par defaut/courante)
	 *
	 * @return integer Timestamp convertit
	 */
	function toLocalizedTimestamp( $utc_timestamp = null, $to_timezone = null ) {
		if( is_null( $utc_timestamp ) )
			$utc_timestamp = time();
			
		if( is_null( $to_timezone ) )
			$to_timezone = getTimezone();
		
		$conv = timezoneconv( $to_timezone );
		if( is_null( $conv ) )
			return $utc_timestamp;
			
		return ( $utc_timestamp + ( 3600 * (int)$conv[ 'offset' ] ) );
	}
	
	
	/**
	 * Convertit un timestamp localis?? en timestamp unix UTC en fonction de la zone horaire
	 *
	 * @param integer $localized_timestamp	Timestamp localis?? ?? re-convertir
	 * @param string $from_timezone			Nom de la zone horaire ayant servit ?? la localisation, NULL par defaut (zone horaire par defaut/courante)
	 *
	 * @return integer Timestamp convertit
	 */
	function toUtcTimestamp( $localized_timestamp, $from_timezone = null ) {
		if( is_null( $from_timezone ) )
			$from_timezone = getTimezone();
		
		$conv = timezoneconv( $from_timezone );
		if( is_null( $conv ) )
			return $localized_timestamp;
			
		return ( $localized_timestamp - ( 3600 * (int)$conv[ 'offset' ] ) );
	}
	
	
	/**
	 * Retourne l'heure du lev?? du soleil ?? la date et aux coordonn??es g??ographique sp??cifi??es
	 *
	 * @param integer $timestamp	Timestamp unix de la date souhait??e
	 * @param float $lat			Latitude
	 * @param float $lon			Longitude
	 *
	 * @return integer Timestamp unix repr??sentant l'heure du lev?? du soleil
	 */
	function sunriseToTimestamp( $timestamp, $lat, $long ) {
		return date_sunrise( $timestamp, SUNFUNCS_RET_TIMESTAMP, $lat, $long, 90.5 );
	}
	
	
	/**
	 * Retourne l'heure du couch?? du soleil ?? la date et aux coordonn??es g??ographique sp??cifi??es
	 *
	 * @param integer $timestamp	Timestamp unix de la date souhait??e
	 * @param float $lat			Latitude
	 * @param float $lon			Longitude
	 *
	 * @return integer Timestamp unix repr??sentant l'heure du couch?? du soleil
	 */
	function sunsetToTimestamp( $timestamp, $lat, $long, $country_code = '' ) {
		return date_sunset( $timestamp, SUNFUNCS_RET_TIMESTAMP, $lat, $long, 90.5 );
	}
	
	
	/**
	 * Retourne le num??ro du premier jour de la semaine
	 *
	 * @return integer Num??ro du jour de la semaine correspondant ?? l'index du tableau de jour DOW_DAYS
	 */
	function getFirstDayOfWeek() {
		$locale = getLanguage();
		$timezone = getTimezone();
		$cal = \IntlCalendar::createInstance( $timezone, $locale );
		
		return $cal->getFirstDayOfWeek() - 1;
	}
	
	
	/**
	 * Retourne un tableau contenant les jours de la semaine positionn??s en fonction de la localisation
	 *
	 * @param string $format	Format du nom des jours: 'SHORT_FORMAT' ou 'LONG_FORMAT'
	 *
	 * @return array	Tableau contenant les jours de la semaine.
	 *					En fonction de la localisation, le premier jour de la semaine
	 *					pourra etre Lundi, Dimanche ou autres
	 */
	function getWeekDays( $format = 'LONG_FORMAT' ) {
		if( !array_key_exists( $format, DOW_DAYS ) )
			return array();
	
		$first_day_of_week = getFirstDayOfWeek();
	
		$days = DOW_DAYS[ $format ];
		for( $i = 0; $i < $first_day_of_week; $i++ ) {
			$day = array_shift( $days );
			array_push( $days, $day );
		}
		
		return array_map( function( $day ) {
			return ( $format == 'LONG_FORMAT'
				? longDayNameToLocalName( $day ) 
				: shortDayNameToLocalName( $day ) );
		}, $days );
	}
	
	
	/**
	 * Retourne le num??ro du jour de la semaine en fonction de la date d??sir??e
	 *
	 * @param integer $timestamp	Timestamp unix de la date d??sir??e
	 *
	 * @return integer 	Num??ro du jour de la semaine commencant ?? 0 pour Dimanche (compatible avec le tableau DOW_DAYS)
	 */
	 function getDayNumberOfWeek( $timestamp = null ) {
		if( is_null( $timestamp ) )
			$timestamp = time();
			
		return idate( 'w', $timestamp );
	 }
	 
	 
	 /**
	 * Retourne le nom du jour de la semaine en fonction de la date et du format d??sir??
	 *
	 * @param integer $timestamp	Timestamp unix de la date d??sir??e
	 * @param string $format		Format du nim du jour: 'SHORT_FORMAT' ou 'LONG_FORMAT'
	 *
	 * @return integer 	Nom du jour de la semaine
	 */
	 function getDayOfWeek( $timestamp = null, $format = 'LONG_FORMAT' ) {
		if( !array_key_exists( $format, DOW_DAYS ) )
			return '';
			
		if( is_null( $timestamp ) )
			$timestamp = time();
			
		$day_number = getDayNumberOfWeek( $timestamp );
		
		return ( $format == 'LONG_FORMAT' 
			? longDayNameToLocalName( DOW_DAYS[ $format ][ $day_number ] )
			: shortDayNameToLocalName( DOW_DAYS[ $format ][ $day_number ] ) );
	 }
	 
	 
	 /**
	 * Retourne les informations d'un jour sp??cifique
	 *
	 * @param integer $timestamp			Timestamp unix de la date d??sir??e
	 * @param DATETIME_FORMAT $date_format	Format de la date humainement lisible
	 *
	 * @return array 	Tableau contenant les informations du jour sp??cifi??
	 */
	 function getDay( $timestamp = null, $date_format = DATETIME_FORMAT[ 'SHORT' ] ) {
		// si le temps unix n'est pas renseign?? alors on utilise le temps actuel
		if( is_null( $timestamp ) )
			$timestamp = time();
	
		// on initialise le 'formatter' pour l'affichage local
		$locale = getLanguage();
		$timezone = getTimezone();
		$formatter = new \IntlDateFormatter( $locale, $date_format, DATETIME_FORMAT[ 'NONE' ], $timezone );
		if( is_null( $formatter ) )
			return array();
		
		// on cr???? un objet DateTimeImmutable pour utiliser le temps unix
		$day = ( new \DateTimeImmutable() )->setTimestamp( $timestamp );
		
		// on initialise le calendrier et on d??finit la date et l'heure locales de travail
		$cal = \IntlCalendar::createInstance( $timezone, $locale );
		$cal->setTime( $timestamp );
		
		// jour de la semaine de 0 ?? 6 (0 pour Dimanche, 6 pour Samedi)
		$day_number			= (int)$day->format( 'w' );
		$current_day		= (int)date( 'd', time() );
		$current_month		= (int)date( 'n', time() );
		$current_year		= (int)date( 'o', time() );

		// suivant la zone horaire et la langue, le premier jour de la semaine change
		// on d??cale donc le tableau des jours en fonction
		$ds = DOW_DAYS[ 'SHORT_FORMAT' ];
		for( $i = 0; $i < getFirstDayOfWeek(); $i++ ) {
			$d = array_shift( $ds );
			array_push( $ds, $d );
		}

		$dn = ( $day_number < 7 )
			? $day_number
			: 0;
		// nom long du jour de la semaine
		$day_long_name		= longDayNameToLocalName( DOW_DAYS[ 'LONG_FORMAT' ][ $dn ] );
		// nom court du jour de la semaine
		$day_short_name		= shortDayNameToLocalName( DOW_DAYS[ 'SHORT_FORMAT' ][ $dn ] );
		
		// correction du num??ro du jour de la semaine en fonction de la localit??
		$day_number = array_search( DOW_DAYS[ 'SHORT_FORMAT' ][ $dn ], $ds ) + getFirstDayOfWeek();
		
		// on fait le m??nage
		unset($ds);
		unset($d);
		
		// on r??cup??re d'autres informations
		$year				= (int)$day->format( 'o' );
		$month				= (int)$day->format( 'n' );
		$day_of_month		= (int)$day->format( 'd' );
		$human_date			= $formatter->format( $day );
		$week_number		= (int)$day->format( 'W' );
		$day_in_year		= (int)$day->format( 'z' ) + 1;

		// on renseigne grace au calendrier si ce jour fait parti du weekend
		$cal->set( $year, $month - 1, $day_of_month );
		$is_weekend 		= $cal->isWeekend();
		
		$is_today			= ( $current_day == $day_of_month && $current_month == $month && $current_year == $year );
		
		// on forme notre jour
		$infos = array(
			'type'				=> 'day',
			'year'				=> $year,
			'month'				=> $month,
			'day_in_week'		=> $day_number,
			'day_in_year'		=> $day_in_year,
			'day_long_name'		=> $day_long_name,
			'day_short_name'	=> $day_short_name,
			'day'				=> $day_of_month,			
			'date'				=> $human_date,
			'is_weekend'		=> $is_weekend,
			'is_today'			=> $is_today,
			'week_number'		=> $week_number,
			'timestamp'			=> $timestamp
		);
		
		// et on fait un peu de m??nage
		unset( $cal );
		unset( $formatter );
		unset( $day );
		
		return $infos;
	 }
	 
	
	/**
	 * Retourne les informations d'une semaine sp??cifique
	 * Contient chaque jour d??taill??s de la semaine en question
	 *
	 * @param integer $timestamp			Timestamp unix de la date d??sir??e
	 * @param DATETIME_FORMAT $date_format	Format de la date humainement lisible
	 * @param boolean $only_same_month		Inclure seulement les jours du mois courant. Faux (false) par default
	 *
	 * @return array 	Tableau contenant les informations de la semaine sp??cifi??e
	 */
	function getWeek( $timestamp = null, int $date_format = DATETIME_FORMAT[ 'SHORT' ], $only_same_month = false ) {
		if( is_null( $timestamp ) )
			$timestamp = time();
	
		$locale = getLanguage();
		$timezone = getTimezone();
			
		$cal = \IntlCalendar::createInstance( $timezone, $locale );
		$cal->setTime( $timestamp );
		$day_number = getDayNumberOfWeek( $timestamp );
		$first_day_of_week = $cal->getFirstDayOfWeek() - 1;
		$diff = $day_number - $first_day_of_week;
		if($diff < 0)
			$diff = 6;
			
		unset( $cal );
		
		$start 			= ( new \DateTimeImmutable() )->setTimestamp( strtotime( '-' . $diff . ' days', $timestamp ) );
		$end 			= ( new \DateTimeImmutable() )->setTimestamp( strtotime( '+7 days', $start->getTimestamp() ) );
		$interval 		= new \DateInterval('P1D');
		$range 			= new \DatePeriod( $start, $interval, $end );
		
		$week_number 	= (int)IntlDateFormatter::formatObject( $start, 'w' );
		$current 		= ( new \DateTimeImmutable() )->setTimestamp( $timestamp );
		$work_month 	= (int)$current->format( 'n' );
		
		$year			= (int)$current->format( 'o' );
		$month			= (int)$current->format( 'n' );
		
		unset( $current );
		
		$week = array();
		foreach($range as $day) {
				$infos = getDay( $day->getTimestamp(), $date_format );
				$infos[ 'in_month' ] = ( $infos[ 'month' ] == $work_month );

				if( !$only_same_month || ( $only_same_month && ( $infos[ 'month' ] == $work_month ) ) )
					$week[] = $infos;
				
				unset( $infos );
		}
		
		return array(
			'type'		=> 'week',
			'year'		=> $year,
			'month'		=> $month,
			'number'	=> $week_number,
			'days'		=> $week
		);
	}
	
	
	/**
	 * Retourne les informations d'une semain en fonction de l'ann??e et de son num??ro
	 *
	 * @param integer $year			Ann??e
	 * @param integer $week_number	Num??ro de la semaine
	 * @param DATETIME_FORMAT $date_format	Format de la date humainement lisible
	 *
	 * @return array Tableau contenant les informations de la semaine sp??cifi??e
	 */
	function getWeekFromNumber( $year, $week_number, int $date_format = DATETIME_FORMAT[ 'SHORT' ] ) {
		$date = new DateTime( 'midnight' );
		$date->setISODate( $year, $week_number );
	
		return getWeek( $date->getTimestamp(), $date_format );
	}
	
	
	/**
	 * Retourne les informations d'un mois sp??cifique
	 * Contient chaque semaine et chaque jours d??taill??s
	 *
	 * @param integer $timestamp			Timestamp unix de la date d??sir??e
	 * @param DATETIME_FORMAT $date_format	Format de la date humainement lisible
	 * @param boolean $only_month_days		Inclure seulement les jours du mois courant. Faux (false) par default
	 *
	 * @return array 	Tableau contenant les informations du mois sp??cifi??
	 */
	function getMonth( $timestamp = null, int $date_format = DATETIME_FORMAT[ 'SHORT' ], $only_month_days = false ) {
		if( is_null( $timestamp ) )
			$timestamp = time();
	
		$locale = getLanguage();
		$timezone = getTimezone();
		
		$dti = ( new \DateTimeImmutable() )->setTimestamp( $timestamp );
		$dti->setTimezone( new \DateTimeZone( $timezone ) );
		$month = (int)$dti->format( 'n' );
		$year = (int)$dti->format( 'o' );
		$number_of_days = (int)$dti->format( 't' );
		$month_name = shortMonthNameToLocalName( $dti->format( 'M' ) );
		$month_long_name = longMonthNameToLocalName( $dti->format( 'F' ) );
	
		$weeks = array();
		for($day = 1; $day <= 31; $day = $day + 7) {
			$weeks[] = getWeek( strtotime( $year . '-' . $month . '-' . $day ), $date_format, $only_month_days );
		}
		
		return array(
			'type'				=> 'month',
			'year'				=> $year,
			'number' 			=> $month,
			'number_of_days'	=> $number_of_days,
			'month_long_name'	=> $month_long_name,
			'month_short_name'	=> $month_name,
			'timestamp'			=> $timestamp,
			'weeks'				=> $weeks
		);
	}
	
	
	/**
	 * Retourne les informations d'une ann??e sp??cifique
	 * Contient chaque mois, semaines et jours d??taill??s
	 *
	 * @param integer $timestamp			Timestamp unix de la date d??sir??e
	 * @param DATETIME_FORMAT $date_format	Format de la date humainement lisible
	 *
	 * @return array 	Tableau contenant les informations de l'ann??e sp??cifi??e
	 */
	function getYear( $timestamp = null, int $date_format = DATETIME_FORMAT[ 'SHORT' ] ) {
		if( is_null( $timestamp ) )
			$timestamp = time();
	
		$locale = getLanguage();
		$timezone = getTimezone();
		
		$dti = ( new \DateTimeImmutable() )->setTimestamp( $timestamp );
		$dti->setTimezone( new \DateTimeZone( $timezone ) );
		$year = (int)$dti->format( 'o' );
		$is_leap_year = isLeapYear( $year );
	
		$monthes = array();
		for($month = 1; $month <= 12; $month++) {
			$monthes[] = getMonth( strtotime( $year . '-' . $month . '-1' ), $date_format, true );
		}
		
		return array(
			'type'				=> 'year',
			'year'				=> $year,
			'number_of_days'	=> ( $is_leap_year ? 366 : 365 ),
			'is_leap_year'		=> $is_leap_year,
			'timestamp'			=> $timestamp,
			'monthes'			=> $monthes
		);
	}
	
	
	/**
	 * Retourne si une ann??e est bisextile
	 *
	 * @param integer $year	Ann??e en question
	 *
	 * @return boolean Vrai (true) si l'ann??e est bisextile, sinon Faux (false)
	 */
	function isLeapYear( $year ) {
		return ( $year % 4 == 0 && ( $year % 100 != 0 || $year % 400 == 0 ) );
	}
	
	
	/**
	 * Retourne la date et l'heure d'un serveur NTP
	 *
	 * @param mixed $time_servers	Liste de serveur NTP
	 *
	 * @return integer	Timestamp de la date et l'heure actuelle retourn??e
	 */
	function getNTP( $time_servers = array( "time.nist.gov",
											"nist1.datum.com",
											"time-a.timefreq.bldrdoc.gov",
											"utcnist.colorado.edu",
											"fr.pool.ntp.org" ) ) {

		$timeout			= 15;
		$time_servers_port	= 37;
		$valid_response 	= false;
		$ts_count 			= sizeof( $time_servers );
		$time_adjustment 	= (int)( (int)date( 'O', time() ) ) / 100;

		for( $i = 0; $i < $ts_count; $i++ ) {
		
			$time_server = $time_servers[ $i ];
			$fp = @fsockopen( $time_server, $time_servers_port, $errno, $errstr, $timeout );
			if( !$fp )
				continue;
			
			$data = null;
			while( !feof( $fp ) )
				$data .= fgets( $fp, 128 );
			
			fclose( $fp );

			if( strlen( $data ) == 4 ) {
				$valid_response = true;
				break;
			}
			
		}

		if( $valid_response ) {
			
			$NTPtime = ord( $data[ 0 ] ) * pow( 256, 3 ) + ord( $data[ 1 ] ) * pow( 256, 2 ) + ord( $data[ 2 ] ) * 256 + ord( $data[ 3 ] );

			// convert the seconds to the present date & time
			// 2840140800 = Thu, 1 Jan 2060 00:00:00 UTC
			// 631152000  = Mon, 1 Jan 1990 00:00:00 UTC
			$TimeFrom1990 = $NTPtime - 2840140800;
			$TimeNow = $TimeFrom1990 + 631152000;

			return $TimeNow + $time_adjustment;
			
		}
		
		return time();
	}
	
	
	/**
	 * Traduit un message texte en fonction du dictionnaire souhait?? (domain)
	 *
	 * @param string $message	Message texte ?? traduire
	 * @param string $domain	Nom du catalogue
	 *
	 * @return string Message traduit
	 */
	function __( $message, $domain = null ) {
		if( is_null( $domain ) )
			return gettext( $message );
		elseif( is_string( $domain ) )
			return dgettext( $domain, $message );
		else
			return $message;
	}
	
	
	/**
	 * Traduit un message texte en fonction du dictionnaire souhait?? (domain)
	 * @see __
	 *
	 * @param string $message	Message texte ?? traduire
	 * @param string $domain	Nom du catalogue
	 *
	 * @return string Message traduit
	 */
	function _e( $message, $domain = null ) {
		return __( $message, $domain );
	}
	
	
	/**
	 * Traduit un nombre mon??taire en fonction de la localisation courante
	 *s
	 * @param mixed $number	Nombre ?? traduire
	 *
	 * @return mixed Nombre traduit
	 */
	function _m( $number, $locale='fr_FR' ) {
		if( is_numeric( $number ) )
		{
			//return money_format( '%i', $number );
			$fmt = new NumberFormatter( $locale, NumberFormatter::CURRENCY );
			//$localmonnaie = array();
			//$locale = $locale .'UTF-8@euro';
			//setlocale(LC_ALL,$locale);
			//$localmonnaie = localeconv();
			//return $fmt->formatCurrency($number, "EUR")."\n";
			
			$adminFormatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
			$symbol = $adminFormatter->getSymbol(\NumberFormatter::INTL_CURRENCY_SYMBOL); // got USD
			return $fmt->formatCurrency($number, $symbol)."\n";
		}
		else
			return $number;
	}
	
	
	/**
	 * Traduit un nombre en fonction de la localisation courante
	 *
	 * @param mixed $number	Nombre ?? traduire
	 *
	 * @return mixed Nombre traduit
	 */
	function _n( $number ) {
		if( is_numeric( $number ) ) {
		
			$conv = localeconv();
			
			return number_format( $number, $conv[ 'frac_digits' ], $conv[ 'decimal_point' ], $conv[ 'thousands_sep' ] );
		}
		else
			return $number;
	}

	
	/**
	 * Traduit une date et une heure au format local r??glable
	 *
	 * @param integer $timestamp			Timestamp unix de la date d??sir??e
	 * @param DATETIME_FORMAT $date_format	Format de la date humainement lisible
	 * @param DATETIME_FORMAT $time_format	Format de l'heure humainement lisible
	 *
	 * @return string 	Date et Heures mis en forme
	 */
	function _dt( $timestamp = null, $date_format = DATETIME_FORMAT[ 'SHORT' ], $time_format = DATETIME_FORMAT[ 'SHORT' ] ) {
		if( is_null( $timestamp ) )
			$timestamp = time();
			
		$locale = setlocale( LC_ALL, 0 );
		$locale = substr($locale, 0, 5);
		$timezone = date_default_timezone_get();
		
		$formatter = new \IntlDateFormatter( $locale, $date_format, $time_format, $timezone );
		if( is_null( $formatter ) )
			return $timestamp;
		
		return $formatter->format( $timestamp );
	}
	
	/**
	 * Traduit une date et une heure au format d??sir?? par une zone horaire
	 *
	 * @param integer $timestamp			Timestamp unix de la date d??sir??e
	 * @param integer $timezone				Zone horaire n??cessaire ?? la traduction
	 * @param DATETIME_FORMAT $date_format	Format de la date humainement lisible
	 * @param DATETIME_FORMAT $time_format	Format de l'heure humainement lisible
	 *
	 * @return string 	Date et Heures mis en forme
	 */
	function _tz( $timestamp = null, $timezone = 'UTC', $date_format = DATETIME_FORMAT[ 'SHORT' ], $time_format = DATETIME_FORMAT[ 'SHORT' ] ) {
		if( is_null( $timestamp ) )
			$timestamp = time();
			
		$locale = setlocale( LC_ALL, 0 );
		$locale = substr($locale, 0,5);
		
		$formatter = new \IntlDateFormatter( $locale, $date_format, $time_format, $timezone );
		if( is_null( $formatter ) )
			return $timestamp;
		
		return $formatter->format( $timestamp );
	}
	
	
	/**
	 * Traduit une heure au format local r??glable
	 *
	 * @param integer $timestamp			Timestamp unix de la date d??sir??e
	 * @param DATETIME_FORMAT $format		Format de l'heure humainement lisible
	 *
	 * @return string 	Heures mis en forme
	 */
	function _t( $timestamp = null, $format = DATETIME_FORMAT[ 'SHORT' ] ) {
		return _dt( $timestamp, \IntlDateFormatter::NONE, $format );
	}
	
	
	/**
	 * Traduit une date au format local r??glable
	 *
	 * @param integer $timestamp			Timestamp unix de la date d??sir??e
	 * @param DATETIME_FORMAT $format		Format de la date humainement lisible
	 *
	 * @return string 	Date mis en forme
	 */
	function _d( $timestamp = null, $format = DATETIME_FORMAT[ 'SHORT' ] ) {
		return _dt( $timestamp, $format, \IntlDateFormatter::NONE );
	}
	
	


?>
