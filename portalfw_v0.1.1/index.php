<?php
	/**
	* PortalFW - Framework PHP pour la conception d'un portail personnel
	*
	* /index.php
	*
	* PHP version 7
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
	*/
	
	
	// Main requirement
	require_once( './core/pfw_base.php' );
	
	//var_export( $_SERVER );
	
	
	$ini = new Ini( './config.ini' );
	/*$ini->datas[ 'locale' ][ 'language' ] = 'fr_FR';
	$ini->datas[ 'locale' ][ 'timezone' ] = 'Europe/Paris';
	$ini->Save();*/
		
	setLanguage( $ini->datas[ 'locale' ][ 'language' ] );
	setTimezone( $ini->datas[ 'locale' ][ 'timezone' ] );
	/*setLanguage( 'en_US' );
	setTimezone( 'America/Chicago' );*/
	//echo $ini->datas['locale']['language']	;



	
	$log = new Log( './logs/' );
	$log->debug( 'locale', __('Unable to load locale module'), $log::GRANT_DAY, __FILE__, __LINE__ );
	$log->info( 'locale', __('Unable to load locale module'), $log::GRANT_DAY, __FILE__, __LINE__ );
	$log->warning( 'locale', __('Unable to load locale module'), $log::GRANT_DAY, __FILE__, __LINE__ );
	$log->error( 'locale', __('Unable to load locale module'), $log::GRANT_DAY, __FILE__, __LINE__ );
	$log->critical( 'locale', __('Unable to load locale module'), $log::GRANT_DAY, __FILE__, __LINE__ );
	
	

	echo '<pre>';
	
	echo sprintf( __( "L'horloge automatique (NTP) nous indique que nous sommes le\t%s" ), _dt( getNTP(), DATETIME_FORMAT[ 'MEDIUM' ], DATETIME_FORMAT[ 'MEDIUM' ] ) ) . '<br />';
	echo sprintf( __( "La configuration locale nous indique que nous sommes le\t\t%s" ), _dt( time(), DATETIME_FORMAT[ 'MEDIUM' ], DATETIME_FORMAT[ 'MEDIUM' ] ) ) . '<br /><br />';
	
	echo __( "Votre configuration locale nous indique aussi:" ) . '<br /><br />';
	echo __( "\tLangue:\t\t\t" ) . getLanguage() . '<br />';
	echo __( "\tLangue (complète):\t" ) . getLanguageEx()[ 'language' ] . '<br />';
	echo __( "\tPays (complet):\t\t" ) . getLanguageEx()[ 'region' ] . '<br />';
	echo __( "\tZone horaire:\t\t" ) . getTimezone() . '<br />';
	echo __( "\tNombre:\t\t\t" ) . _n( 1456.258 ) . '<br />';
	echo __( "\tMonnaie:\t\t" ) . _m( 20.99, $ini->datas['locale']['language'] ) . '<br />';
	echo __( "\tDate et Heure:\t\t" ) . _dt( time(), true, DATETIME_FORMAT[ 'LONG' ], DATETIME_FORMAT[ 'SHORT' ] ) . '<br />';
	
	echo '<br /></pre>';
	
	// calendar
	$month = getMonth();
	echo '<table style="font-size: 0.9em; font-family: sans-serif; margin-left: 50px;">';
	echo '<tr style="background-color: #eee;"><td colspan="8" style="text-align: center; font-weight: bold; padding: 0.2em;">' . $month[ 'month_long_name' ] . ' ' . $month[ 'year' ] . '</td></tr>';
	echo '<tr><td style="background-color: #fff; padding: 0.1em;"></td>';
	for( $dn = 0; $dn < 7; $dn++ ) {
		$d = $dn + getFirstDayOfWeek();
		if( $d > 6 ) $d = 0;
		echo '<td style="color: #555; background-color: #eee; padding: 0.2em; font-size: 0.8em; vertical-align: middle; text-align: center;">' . shortDayNameToLocalName( DOW_DAYS[ 'SHORT_FORMAT' ][ $d ] ) . '</td>';
	}
	echo '</tr>';
	for( $w = 0; $w < count( $month[ 'weeks' ] ); $w++) {
		echo '<tr><td style="color: #555; background-color: #eee; padding: 0.2em; font-size: 0.8em; vertical-align: middle; text-align: center;">' . $month[ 'weeks' ][ $w ][ 'number' ] . '</td>';
		
		for( $d = 0; $d < count( $month[ 'weeks' ][ $w ][ 'days' ] ); $d++) 
			echo '<td style="color: ' . ( $month[ 'weeks' ][ $w ][ 'days' ][ $d ][ 'is_today' ] ? 'red' : ( $month[ 'weeks' ][ $w ][ 'days' ][ $d ][ 'in_month' ] ? '#000' : '#ccc' ) ) . '; background-color: ' . ( $month[ 'weeks' ][ $w ][ 'days' ][ $d ][ 'is_weekend' ] ? '#eee' : '#fff' ) . '; padding: 0.2em; font-size: 1.0em; vertical-align: middle; text-align: center;">' . $month[ 'weeks' ][ $w ][ 'days' ][ $d ][ 'day' ] . '</td>';
		
		echo '</tr>';
	}
	echo '</table>';
	
	echo '<pre>';
	
	// geo
	$net = ipToCoordsGeoPlugin();

	if( !is_null( $net ) ) {
		echo '<br />' . __( "Vos informations de connexion:" ) . '<br /><br />';
		echo __( "\tAdresse IP V4:\t\t") . $net[ 'request' ] . '<br />';
		echo __( "\tEmplacement:\t\t" ) . $net[ 'city' ] . ', ' . $net[ 'regionCode' ] . ' ' . $net[ 'regionName' ] . ', ' . $net[ 'region' ] . ', ' . $net[ 'countryName' ] . '<br />';
		echo __( "\tPosition géographique:\tlat " ) .$net[ 'latitude' ] . ', lon ' . $net[ 'longitude' ] . ' (' . decToDD( $net[ 'latitude' ], $net[ 'longitude' ] ) . ')<br />';
		echo __( "\tZone horaire:\t\t" ) . $net[ 'timezone' ] . '<br />';
		echo __( "\tSymbole monétaire:\t" ) . $net[ 'currencySymbol_UTF8' ] . ' (' . $net[ 'currencyCode' ] . ')<br />';
	}
	
	$city = "beuvry la forêt";
	$coords = searchForCoords( $city );
	if( !is_null( $coords )
		&& is_array( $coords )
		&& count( $coords ) > 0
		&& isset( $coords[ 0 ][ 'lat' ] )
		&& isset( $coords[ 0 ][ 'lon' ] )
		&& isset( $coords[ 0 ][ 'address' ] )
		&& isset( $coords[ 0 ][ 'address' ][ 'country_code' ] ) ) {
		
		$lat = $coords[ 0 ][ 'lat' ];
		$lon = $coords[ 0 ][ 'lon' ];
		$country_code = $coords[ 0 ][ 'address' ][ 'country_code' ];
		
		$timezone = getNearestTimezone( $lat, $lon, $country_code );
		echo '<br /><br />' . sprintf( __( "A %s (zone horaire correspondante: %s):" ), $city, $timezone ) . '<br /><br />';
		
		$ts1 = sunriseToTimestamp( time(), $lat, $lon, $country_code );
		$ts2 = sunsetToTimestamp( time(), $lat, $lon, $country_code );		
		
		echo sprintf( __( "\tLe soleil se lève à %s (heure locale)." ),
			_tz( $ts1, $timezone, DATETIME_FORMAT[ 'NONE' ], DATETIME_FORMAT[ 'LONG' ] )
		) . '<br />';
		
		echo sprintf( __( "\tLe soleil se couche à %s (heure locale)." ),
			_tz( $ts2, $timezone, DATETIME_FORMAT[ 'NONE' ], DATETIME_FORMAT[ 'LONG' ] )
		) . '<br />';
	}
	
	$query = "mairie de beuvry la forêt";
	$coords = searchForCoords( $query );
	if( !is_null( $coords )
		&& is_array( $coords )
		&& count( $coords ) > 0
		&& isset( $coords[ 0 ][ 'lat' ] )
		&& isset( $coords[ 0 ][ 'lon' ] )
		&& isset( $coords[ 0 ][ 'address' ] )
		&& isset( $coords[ 0 ][ 'display_name' ] )
		&& isset( $coords[ 0 ][ 'address' ][ 'country_code' ] )
		&& isset( $coords[ 0 ][ 'address' ][ 'country' ] )
		&& ( isset( $coords[ 0 ][ 'address' ][ 'city' ]) 
			|| isset( $coords[ 0 ][ 'address' ][ 'state' ] ) ) ) {
		
		echo '<br /><br />' . sprintf( __( "Vous recherchez '%s' et nous avons trouvé:" ), $query ) . '<br /><br />';
		
		$lat = $coords[ 0 ][ 'lat' ];
		$lon = $coords[ 0 ][ 'lon' ];
		$country_code = $coords[ 0 ][ 'address' ][ 'country_code' ];
		$country = $coords[ 0 ][ 'address' ][ 'country' ];
		$city = ( isset( $coords[ 0 ][ 'address' ][ 'city' ] ) 
			? $coords[ 0 ][ 'address' ][ 'city' ] 
			: ( isset( $coords[ 0 ][ 'address' ][ 'state' ] ) 
				? $coords[ 0 ][ 'address' ][ 'state' ]
				: '' ) );
		$address = $coords[ 0 ][ 'display_name' ];
		
		echo sprintf( __( "\tVille:\t\t\t%s" ), $city ) . '<br />';
		echo sprintf( __( "\tPays:\t\t\t%s" ), $country ) . '<br />';
		echo sprintf( __( "\tAdresse postale:\t%s" ), $address ) .'<br />';
		echo sprintf( __( "\tEmplacement:\t\tlat %s, lon %s" ), $lat, $lon ) . '<br />';
		
		$timezone = getNearestTimezone( $lat, $lon, $country_code );
		echo sprintf( __("\tZone horaire:\t\t%s"), $timezone ) . '<br />';
		
		$query = sprintf( "cafe %s, %s", $lat, $lon );
		$coords = searchForCoords( $query, 4 );
		if( !is_null( $coords )
		&& is_array( $coords )
		&& count( $coords ) > 0 ) {

			echo '<br />' . __( "\tLes 4 cafés/pubs les plus proches:" ) . '<br /><br />';
			foreach($coords as $cafe) {
				
				if( !is_null( $cafe )
					&& is_array( $cafe )
					&& count( $cafe ) > 0
					&& isset( $cafe[ 'lat' ] )
					&& isset( $cafe[ 'lon' ] )
					&& isset( $cafe[ 'display_name' ] ) ) {
					
					$lat = $cafe[ 'lat' ];
					$lon = $cafe[ 'lon' ];
					$address = $cafe[ 'display_name' ];
					$name = ( isset( $cafe[ 'namedetails'][ 'name' ] )
						? $cafe[ 'namedetails'][ 'name' ]
						: ( isset( $cafe[ 'address' ][ 'cafe' ] ) 
							? $cafe[ 'address' ][ 'cafe' ]
							: ( isset( $cafe[ 'address' ][ 'pub' ] )
								? $cafe[ 'address' ][ 'pub' ]
								: '' ) ) );
					$email = isset( $cafe[ 'extratags' ][ 'email' ] ) 
						? $cafe[ 'extratags' ][ 'email' ]
						: '';
					$phone = isset( $cafe[ 'extratags' ][ 'phone' ] ) 
						? $cafe[ 'extratags' ][ 'phone' ]
						: '';
					$website = isset( $cafe[ 'extratags' ][ 'website' ] ) 
						? $cafe[ 'extratags' ][ 'website' ]
						: '';
					$opening_hours = isset( $cafe[ 'extratags' ][ 'opening_hours' ] ) 
						? $cafe[ 'extratags' ][ 'opening_hours' ]
						: '';
					
					echo sprintf( __( "\t\tNom:\t\t\t%s" ), $name ) .'<br />';
					echo sprintf( __( "\t\tEmail:\t\t\t%s" ), $email ) .'<br />';
					echo sprintf( __( "\t\tTéléphone:\t\t%s" ), $phone ) .'<br />';
					echo sprintf( __( "\t\tSite internet:\t\t%s" ), $website ) .'<br />';
					echo sprintf( __( "\t\tHoraires:\t\t%s" ), $opening_hours ) .'<br />';
					echo sprintf( __( "\t\tAdresse postale:\t%s" ), $address ) .'<br />';
					echo sprintf( __( "\t\tEmplacement:\t\tlat %s, lon %s" ), $lat, $lon ) . '<br />';					
					echo '<br />';
					
				}
				
			}
			
		}
		
	}
	
	echo '</pre>';
	
?>
