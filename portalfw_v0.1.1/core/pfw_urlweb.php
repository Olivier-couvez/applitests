<?php
	/**
	* PortalFW - Framework PHP pour la conception d'un portail personnel
	*
	* /core/pfw_urlweb.php
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
	

	/**
	 * Récupère le contenu brut d'une page web
	 *
	 * @param	$url					Url de la page à récupérer
	 * @param	$maximumRedirections	Nombre de redirection maximum (null par defaut, soit 1)
	 * @param	$currentRedirection		Nombre de redirections déja effectuées
	 *
	 * @return	string					Contenu de la page web
	 */
	function getUrlContents( $url, $maximumRedirections = null, $currentRedirection = 0 )
	{
		$result 	= false;
		$contents 	= '';
		
		if( extension_loaded( 'curl' ) && function_exists( 'curl_init' ) ) {
		
			$curl = curl_init();
			curl_setopt( $curl, CURLOPT_URL, $url );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $curl, CURLOPT_USERAGENT, 'PortalFW' );
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			
			$contents = curl_exec( $curl );
			
			curl_close( $curl );
			
		} else if( ini_get( 'allow_url_fopen' ) ) {
		
			$contents = @file_get_contents( $url, 'r' );
			
		}
		
		if( !$contents )
			$contents = '';

		if( isset( $contents ) && is_string( $contents ) ) {
			preg_match_all(
				'/<[\s]*meta[\s]*http-equiv="?REFRESH"?' . '[\s]*content="?[0-9]*;[\s]*URL[\s]*=[\s]*([^>"]*)"?' . '[\s]*[\/]?[\s]*>/si',
				$contents, $match );
		
			if( isset( $match ) 
				&& is_array( $match )
				&& count( $match ) == 2
				&& count( $match[ 1 ] ) == 1 ) {
				
				if ( !isset($maximumRedirections ) || $currentRedirection < $maximumRedirections )
					return getUrlContents( $match[ 1 ][ 0 ], $maximumRedirections, ++$currentRedirection );
			
				$result = false;
				
			} else
				$result = $contents;
			
		}
	
		return $result;
	}
	
	
	/**
	 * Récupère les informations d'une page web
	 *
	 * @param	$url	Url de la page à récupérer
	 *
	 * @return	mixed	Tableau contenant les informations
	 */
	function getUrlDatas( $url ) {
		$result = false;
	
		$contents = getUrlContents( $url );

		if( isset( $contents ) && is_string( $contents ) ) {
			$title 		= null;
			$metaTags 	= null;
		
			preg_match(
				'/<title>([^>]*)<\/title>/si',
				$contents, $match );

			if( isset( $match ) && is_array( $match ) && count( $match ) > 0 )
				$title = strip_tags( $match[ 1 ] );
		
			preg_match_all(
				'/<[\s]*meta[\s]*name="?' . '([^>"]*)"?[\s]*' . 'content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si',
				$contents, $match );
		
			if( isset( $match ) && is_array( $match ) && count( $match ) == 3 ) {
				$originals	= $match[0];
				$names		= $match[1];
				$values		= $match[2];
			
				if( count( $originals ) == count( $names ) && count( $names ) == count( $values ) ) {
				
					$metaTags = array();
				
					for( $i = 0, $limiti = count( $names ); $i < $limiti; $i++ ) {
						$metaTags[ $names[ $i ] ] = array(
							'html'	=> htmlentities( $originals[ $i ] ),
							'value'	=> $values[ $i ]
						);
					}
					
				}
				
			}
		
			$result = array(
				'title' 	=> $title,
				'metaTags'	=> $metaTags
			);
		}
	
		return $result;
	}
	
	
	/**
	 * Indique si le script en courant provient du meme domaine que la page parente
	 *
	 * @return	boolean		Vrai ou Faux
	 */
	function fromSameDomain() {
		if( !isset( $_SERVER[ 'HTTP_REFERER' ] ) || empty( $_SERVER[ 'HTTP_REFERER' ] ) )
			return false;
			
		if( !isset( $_SERVER[ 'REQUEST_URI' ] ) || empty( $_SERVER[ 'REQUEST_URI' ] ) )
			return false;
	
		$referer	= parse_url( $_SERVER[ 'HTTP_REFERER' ],  PHP_URL_HOST );
		$uri		= parse_url( $_SERVER[ 'REQUEST_URI' ],  PHP_URL_HOST );
		
		return ( strtolower( $referer ) == strtolower( $uri ) );
	}
	
	
	/**
	 * Indique si le script en courant provient de la meme page web
	 *
	 * @return	boolean		Vrai ou Faux
	 */
	function fromSameWebpage() {
		if( !isset( $_SERVER[ 'HTTP_REFERER' ] ) || empty( $_SERVER[ 'HTTP_REFERER' ] ) )
			return false;
			
		if( !isset( $_SERVER[ 'REQUEST_URI' ] ) || empty( $_SERVER[ 'REQUEST_URI' ] ) )
			return false;
	
		$referer	= parse_url( $_SERVER[ 'HTTP_REFERER' ],  PHP_URL_PATH );
		$uri		= parse_url( $_SERVER[ 'REQUEST_URI' ],  PHP_URL_PATH );
		
		return ( strtolower( $referer ) == strtolower( $uri ) );
	}

	
	/**
	 * Retrouve la page parente
	 *
	 * @return	string	Url de la page parente
	 */
	function getRefererWebpage() {
		if( !isset( $_SERVER[ 'HTTP_REFERER' ] ) || empty( $_SERVER[ 'HTTP_REFERER' ] ) )
			return '';
			
		return parse_url( $_SERVER[ 'HTTP_REFERER' ],  PHP_URL_PATH );
	}
	
	
	/**
	 * Indique si le script courant provient d'un de ses appels
	 *
	 * @return	boolean		Vrai ou Faux
	 */
	function fromSelf() {
		return ( fromSameDomain() && fromSameWebpage() );
	}
	
	
	/**
	 * Renvoie les arguments passés dans l'url
	 *
	 * @return	mixed	Liste des paramètres
	 */
	function getWebpageQueries() {
		$queries = array();
		
		if( isset( $_SERVER[ 'QUERY_STRING' ] ) && !empty( $_SERVER[ 'QUERY_STRING' ] ) )
			parse_str( $_SERVER[ 'QUERY_STRING' ], $queries );
		
		return $queries;		
	}
	
	
	/**
	 * Renvoie le nom de la page (nom du fichier)
	 *
	 * @return	string	Nom de la page
	 */
	function getWebpageName() {
		$pi = pathinfo( $_SERVER[ 'SCRIPT_FILENAME' ] );
		
		if($pi === NULL)
			return '';
			
		return strtolower( trim( $pi[ 'filename' ] ) );
	}
	
	
	/**
	 * Lit une valeur passée par la méthode GET
	 *
	 * @param	$name		Nom de la variable
	 * @param	$defaut		Valeur par défaut
	 * @param	$strip		Nettoyer la valeur avant de la retourner
	 *
	 * @return	string		Valeur trouvée ou celle définit par défaut
	 */
	function readGetValue( $name, $default, $strip = true ) {
		$val = trim( isset( $_GET[ $name ] ) 
			? $_GET[ $name ]
			: $default );
		if( $strip )
			$val = htmlspecialchars( $val );
		
		return trim( ( $val != '' )
			? $val
			: $default );
	}


	/**
	 * Lit une valeur passée par la méthode POST
	 *
	 * @param	$name		Nom de la variable
	 * @param	$defaut		Valeur par défaut
	 * @param	$strip		Nettoyer la valeur avant de la retourner
	 *
	 * @return	string		Valeur trouvée ou celle définit par défaut
	 */
	function readPostValue( $name, $default, $strip = true ) {
		$val = trim( isset( $_POST[ $name ] ) 
			? $_POST[ $name ]
			: $default );
		if( $strip )
			$val = htmlspecialchars( $val );
		
		return trim( ( $val != '' )
			? $val
			: $default );
	}
	
	
	/**
	 * Renvoie l'adresse IP réelle du visiteur
	 * Traverse les ponts et proxys.
	 *
	 * @return	string		Adresse IP trouvée
	 */
	function getRealIp() {
		$ip = $_SERVER[ 'REMOTE_ADDR' ];
		
		if( isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) 
			&& preg_match_all( '#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER[ 'HTTP_X_FORWARDED_FOR' ], $matches ) ) {
			
			foreach( $matches[ 0 ] AS $xip ) {
				if( !preg_match( '#^(10|172\.16|192\.168)\.#', $xip ) ) {
				
					$ip = $xip;
					break;
					
				}
			}
			
		} elseif( isset( $_SERVER[ 'HTTP_CLIENT_IP' ] )
			&& preg_match( '/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER[ 'HTTP_CLIENT_IP' ] ) ) {
			
				$ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
				
		} elseif( isset( $_SERVER[ 'HTTP_CF_CONNECTING_IP' ] )
			&& preg_match( '/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER[ 'HTTP_CF_CONNECTING_IP' ] ) ) {
			
				$ip = $_SERVER[ 'HTTP_CF_CONNECTING_IP' ];
				
		} elseif( isset( $_SERVER[ 'HTTP_X_REAL_IP' ] )
			&& preg_match( '/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER[ 'HTTP_X_REAL_IP' ] ) ) {
			
				$ip = $_SERVER[ 'HTTP_X_REAL_IP' ];
		}
		
		return $ip;
	}

	
	/**
	 * Utilise le service IP Echo pour trouver l'adresse IP Internet d'un visiteur
	 *
	 * @return	string		Adresse IP trouvée
	 */
	function getExternalIp() {
		return @file_get_contents( 'http://ipecho.net/plain' );
	}
	
	
	/**
	 * Indique si la page est chargée de manière sécurisée
	 *
	 * @return	boolean		Vrai ou Faux
	 */
	function isHttps() {
		return ( !empty( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] !== 'off' || $_SERVER[ 'SERVER_PORT' ] == 443 );
	}
	
	
	/**
	 * Supprime la mise en cache de la page
	 *
	 * @return	void
	 */
	function resetCache() {
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
	}
	
	
	/**
	 * Redirige le visiteur vers une page définit
	 *
	 * @param	$url		Url de la redirection
	 * @param	$nocahe		Supprime au passage la mise ne cache
	 *
	 * @return	void
	 */
	function redirectTo( $url, $nocache = false ) {
		if( $nocache )
			resetCache();
		
		header( 'Location: ' . $url );
		
		die();
	}

	
	/**
	 * Recharge la page en cours
	 *
	 * @param	$nocahe		Supprime au passage la mise ne cache
	 *
	 * @return	void
	 */
	function redirectSelf( $nocache = false ) {
		redirectTo( ( isHttps() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $nocache );
	}
	
	
	/**
	 * Redirige le visiteur vers la page parente (précédente)
	 *
	 * @param	$nocahe		Supprime au passage la mise ne cache
	 *
	 * @return	void
	 */
	function redirectToReferer( $nocache = false ) {
		redirectTo( $_SERVER[ 'HTTP_REFERER' ], $nocache );
	}
	
	
	/**
	 * Retourne l'url complète de la page en cours
	 *
	 * @param	$with_queries	Indique s'il faut renvoyer ausssi les arguments
	 *
	 * @return	string			Url de la page en cours
	 */
	function getSelf( $with_queries = true ) {
		$self = $_SERVER[ 'PHP_SELF' ];
		
		if( $with_queries && isset( $_SERVER[ 'QUERY_STRING' ] ) && !empty( $_SERVER[ 'QUERY_STRING' ] ) )
			$self .= '?' . $_SERVER[ 'QUERY_STRING' ];
		
		return $self;
	}
	
	
	/**
	 * Retourne l'url complète de la page ayant appelé ce script
	 *
	 * @return	string			Url de la page web
	 */
	function from() {
		if( ( isset( $_SERVER[ 'HTTP_REFERER' ] ) && !empty( $_SERVER[ 'HTTP_REFERER' ] ) ) ) {
		
			$f = trim( strtolower( parse_url( $_SERVER[ 'HTTP_REFERER' ], PHP_URL_PATH ) ) );
			
			if( substr( $f, strlen( $f ) - 1, 1 ) == '/' )
				$f = substr( $f, 0, strlen( $f ) - 1 );

			$f = explode( '/', $f );
			$f = $f[ count( $f ) - 1 ];
			
			return $f;
		}
		return '';
	}
	
	
	/**
	 * Vérifie si une url correspond a celle ayant appelé ce script
	 *
	 * @param	$from		Url de la page à tester
	 *
	 * @return	boolean		Vrai ou Faux
	 */
	function isFrom( $from ) { 
		return ( from() == trim( strtolower( $from ) ) );
	}
	
	
	
	
	
	
	
	
	
	
	
	
	

?>
