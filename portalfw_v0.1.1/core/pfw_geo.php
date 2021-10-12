<?php
	/**
	* PortalFW - Framework PHP pour la conception d'un portail personnel
	*
	* /core/pfw_geo.php
	*
	* Fonctions géographiques
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
	* @required	  
	*/
	
	
	/**
	* Constantes de données pour la conversion
	*/
	const METERS_2_KILOMETERS		= array( 1000,		'/', 'm' );
	const METERS_2_MILES			= array( 1609.344,	'/', 'km' );
	const METERS_2_NAUTICAL_MILES	= array( 1852,		'/', 'nm' );
	const MILES_2_FEET				= array( 5280,		'*', 'ft' );
	const MILES_2_YARDS				= array( 1760,		'*', 'yd' );
	
	
	/**
	 * Convertit des distance en plusieurs formats
	 *
	 * @param float 	$value				Valeur à convertir
	 * @param array 	$conversion_data	Constante de données pour la conversion:
	 *										METERS_2_KILOMETERS
	 *										METERS_2_MILES
	 *										METERS_2_NAUTICAL_MILES
	 *										MILES_2_FEET
	 *										MILES_2_YARDS
	 * @param boolean 	$reverse			Inverser la conversion (par defaut: Faux / false)
	 * @param integer 	$round			Arrondissement du résultat (par defaut: 10 chiffres derrière la virgule)
	 * @param boolean 	$join_unit		Joindre l'unité au résultat
	 *
	 * @return mixed					Résultat de la conversion. Retoune un type float ou un type string si l'unité est joint
	 */
	function geoConverter( $value, $conversion_data, $reverse = false, $round = 10, $join_unit = false ) {
		if( !is_numeric( $value ) )
			return $value;
			
		if( !is_array( $conversion_data )
			|| count( $conversion_data ) != 3)
			return $value;
			
		if( !is_numeric( $conversion_data[ 0 ] )
			|| ( $conversion_data[ 1 ] != '/'
			&& $conversion_data[ 1 ] != '*' ) )
			return $value;
			
		if( $reverse ) 
			$conversion_data[ 1 ] = ($conversion_data[ 1 ] == '/') ? '*' : '/';
		
		switch ( $conversion_data[ 1 ] ) {
			case '/':
				$value = $value / $conversion_data[ 0 ];
				break;
			case '*':
				$value = $value * $conversion_data[ 0 ];
				break;
		}
		
		return round( $value, $round ) . ( $join_unit ? ' ' . trim( $conversion_data[ 2 ] ) : '' );
	}
	

	/**
	 * Calcule la distance entre 2 points définit par des coordonnées GPS
	 *
	 * @param float	$first_lat		Point n°1, Latitude
	 * @param float $first_lon		Point n°1, Longitude
	 * @param float $second_lat		Point n°2, Latitude
	 * @param float $second_lon		Point n°2, Longitude
	 * @param float $earth_radius	Diamètre de la Terre (par defaut: 6378137 metres)
	 *
	 * @return float 				Distance en metres
	 */
	function coordsDistance( $first_lat, $first_lon, $second_lat, $second_lon, $earth_radius = 6378137 ) {
		static $_PI_RAD = M_PI / 180;
		
		$first_lat *= $_PI_RAD;
		$first_lon *= $_PI_RAD;
		$second_lat *= $_PI_RAD;
		$second_lon *= $_PI_RAD;
		
		$x = pow( sin( ( $first_lat - $second_lat ) / 2 ), 2 );
		$y = pow( sin( ( $first_lon - $second_lon ) / 2 ), 2 );
		$distance = 2 * asin( sqrt( $x + cos( $first_lat ) * cos( $second_lat ) * $y ) );
		
		return $distance * $earth_radius; // metres
	}
	
	
	/**
	 * Recherche de coordonnées et d'informations géographique
	 * via le service 'Nominatim' du projet 'OpenStreetMap'.
	 *
	 * L'API permet lancer une requète en indiquant une adresse postale,
	 * une ville, des coordonnées GPS, des mots clefs, etc.
	 * eg: adresse postale
	 *
	 * @see https://nominatim.org/release-docs/develop/api/Search/
	 *
	 * @param string	$query			Requète
	 * @param integer	$result_limit	Nombre de résultats à retourner (par defaut: 1)
	 *
	 * @return mixed					Un tableau contenant toutes les informations trouvées, null si pas de résultats
	 */
	function searchForCoords( $query, $result_limit = 1 ) {
		if( !function_exists( 'getUrlContents' ) )
			return null;
	
		$query = urlencode( htmlspecialchars( $query ) );
		
		$language = explode( '.', substr(setlocale( LC_ALL, 0 ),0,5) );
		if( is_array( $language ) && count( $language ) >= 1 )
			$language = $language[ 0 ];
			
		$language = explode( '_', $language );
		if( is_array( $language ) && count( $language ) >= 1 )
			$language = $language[ 0 ];
			
		$language = strtolower( $language );
	
		$url = 'https://nominatim.openstreetmap.org/search?q=%s&format=json&addressdetails=1&extratags=1&namedetails=1&accept-language=%s&limit=%s';
		$url = sprintf( $url, $query, $language, $result_limit );
		$json = getUrlContents( $url );
		if( is_null( $json ) || $json == '' )
			return null;
		
		return json_decode( $json, true );
	}
	
	
	/**
	 * Recherche d'informations géographique par des coordonnées
	 * GPS via le service 'Nominatim' du projet 'OpenStreetMap'.
	 *
	 * @see https://nominatim.org/release-docs/develop/api/Reverse/
	 *
	 * @param float $lat	Latitude
	 * @param float $lon	Longitude
	 *
	 * @return mixed		Un tableau contenant toutes les informations trouvées, null si pas de résultats
	 */
	function searchByCoords( $lat, $lon ) {
		if( !is_numeric( $lat ) || !is_numeric( $lon ) )
			return null;
		
		$language = explode( '.', setlocale( LC_ALL, 0 ) );
		if( is_array( $language ) && count( $language ) >= 1 )
			$language = $language[ 0 ];
			
		$language = explode( '_', $language );
		if( is_array( $language ) && count( $language ) >= 1 )
			$language = $language[ 0 ];
			
		$language = strtolower( $language );
	
		$url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=%s&lon=%s&zoom=18&addressdetails=1&namedetails=1&extratags=1&accept-language=%s";
		$url = sprintf( $url, str_replace( ',', '.', strval( $lat ) ), str_replace( ',', '.', strval( $lon ) ) , $language );
		
		$json = getUrlContents( $url );
		if( is_null( $json ) || $json == '' )
			return null;
		
		return json_decode( $json, true );
	}
	
	
	/**
	 * Retourne les informations relatives à une adresse IP V4
	 *
	 * GeoPlugin WebService
	 * @see http://www.geoplugin.net
	 *
	 * @param mixed $ipv4	Adresse IP V4 à rechercher, NULL pour utiliser son adresse IP
	 *
	 * @return mixed		Retourne un tableau contenant les données ou NULL en cas d'échec
	 */
	function ipToCoordsGeoPlugin( $ipv4 = null ) {
		if( is_null( $ipv4 ) )
			$ipv4 = $_SERVER[ 'REMOTE_ADDR' ];
			if ($ipv4 == "127.0.0.1" || is_null($ipv4)){
				$ipv4 = "212.27.48.10";
			}
		$url = "http://www.geoplugin.net/php.gp?ip=%s";
		$url = sprintf( $url, $ipv4 );
	
		$serialized = getUrlContents( $url );
		if( is_null( $serialized ) || $serialized == '' )
			return null;
		
		$result = json_decode( str_replace( 'geoplugin_', '', json_encode( unserialize( $serialized ) ) ), true );
		if( is_array( $result ) && isset( $result[ 'status' ] ) && $result[ 'status' ] == 200 )
			return $result;
		
		return null;
	}
	
	
	/**
	 * Retourne les informations relatives à une adresse IP V4 et V6
	 *
	 * Geoip DB WebService
	 * @see https://geoip-db.com
	 *
	 * @param mixed $ip		Adresse IP V4 ou V6 à rechercher, NULL pour utiliser son adresse IP
	 *
	 * @return mixed		Retourne un tableau contenant les données ou NULL en cas d'échec
	 */
	function ipToCoordsGeoipDB( $ip = null ) {
		$url = "https://geoip-db.com/json/";
		if( !is_null( $ip ) && is_string( $ip ) )
			$url .= $ip;
	
		$json = getUrlContents( $url );
		if( is_null( $json ) || $json == '' )
			return null;

		return json_decode( $json, true );
	}
	
	
	/**
	 * Retourne les informations relatives à une adresse IP V4
	 *
	 * IPWHOIS WebService
	 * @see https://ipwhois.io
	 *
	 * @param mixed $ipv4	Adresse IP V4 à rechercher, NULL pour utiliser son adresse IP
	 *
	 * @return mixed		Retourne un tableau contenant les données ou NULL en cas d'échec
	 */
	function ipToCoordsIPWHOIS( $ipv4 = null ) {
		if( is_null( $ipv4 ) )
			$ipv4 = $_SERVER[ 'REMOTE_ADDR' ];
	
		$url = "http://free.ipwhois.io/json/%s";
		$url = sprintf( $url, $ipv4 );
	
		$json = getUrlContents( $url );
		if( is_null( $json ) || $json == '' )
			return null;

		$result = json_decode( $json, true );
		if( is_array( $result ) && isset( $result[ 'success' ] ) && $result[ 'success' ] )
			return $result;
			
		return null;
	}
	
	
	/**
	 * Convertit les coordonnées GPS décimales en Degrées Minutes Secondes
	 *
	 * @param	$lat	Latitude en décimal
	 * @param	$lon	Longitude en décimal
	 *
	 * @return string	Retourne les coordonnées sous le format DD°MM'SS.SSSSS
	 */
	function decToDMS( $lat, $lon ) {
		$H = 'E';
		$V = 'N';
		
		if( $lat < 0 ) {
			$V = 'S';
			$lat = -$lat;
		}
		if( $lon < 0 ) {
			$H = 'W';
			$lon = -$lon;
		}
		
		$lat_degree = (int)$lat;
		$lat_minutes = ( $lat - $lat_degree ) * 60;
		$lat_seconds = $lat_minutes;
		$lat_minutes = (int)$lat_minutes;
		$lat_seconds = round( ( $lat_seconds - $lat_minutes ) * 60, 5 );
		
		$lon_degree = (int)$lon;
		$lon_minutes = ( $lon - $lon_degree ) * 60;
		$lon_seconds = $lon_minutes;
		$lon_minutes = (int)$lon_minutes;
		$lon_seconds = round( ( $lon_seconds - $lon_minutes ) * 60, 5 );
	
		return str_replace( ',', '.', sprintf( "%s°%s'%s\" %s %s°%s'%s\" %s",
			$lat_degree, $lat_minutes, $lat_seconds, $V,
			$lon_degree, $lon_minutes, $lon_seconds, $H ) );
	}
	
	
	/**
	 * Convertit les coordonnées GPS décimales en Degrées Minutes
	 *
	 * @param	$lat	Latitude en décimal
	 * @param	$lon	Longitude en décimal
	 *
	 * @return string	Retourne les coordonnées sous le format DD°MM'
	 */
	function decToDM( $lat, $lon ) {
		$H = '';
		$V = '';
		
		if( $lat < 0 ) {
			$V = '-';
			$lat = -$lat;
		}
		if( $lon < 0 ) {
			$H = '-';
			$lon = -$lon;
		}
		
		$lat_degree = (int)$lat;
		$lat_minutes = round( ( $lat - $lat_degree ) * 60, 5 );
		
		$lon_degree = (int)$lon;
		$lon_minutes = round( ( $lon - $lon_degree ) * 60, 5 );
	
		return str_replace( ',', '.', sprintf( "%s%s°%s' %s%s°%s'",
			$V, $lat_degree, $lat_minutes,
			$H, $lon_degree, $lon_minutes ) );
	}
	
	
	/**
	 * Convertit les coordonnées GPS décimales en Degrées
	 *
	 * @param	$lat	Latitude en décimal
	 * @param	$lon	Longitude en décimal
	 *
	 * @return string	Retourne les coordonnées sous le format DD.DDDDDDDDD°
	 */
	function decToDD( $lat, $lon ) {
		$H = 'E';
		$V = 'N';
		
		if( $lat < 0 ) {
			$V = 'S';
			$lat = -$lat;
		}
		if( $lon < 0 ) {
			$H = 'W';
			$lon = -$lon;
		}
	
		return str_replace( ',', '.', sprintf( "%s° %s %s° %s",
			round( $lat, 5 ), $V,
			round( $lon, 5 ), $H ) );
	}
	




?>
