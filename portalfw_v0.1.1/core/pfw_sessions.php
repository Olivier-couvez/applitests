<?php
	/**
	* PortalFW - Framework PHP pour la conception d'un portail personnel
	*
	* /core/pfw_sessions.php
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
	* @since      File available since Release 0.2.0
	*/
	
	
	/**
	 * Détermine si une session est active
	 *
	 * @return	boolean		Vrai ou Faux
	 */
	function isSessionStarted() {
		if( php_sapi_name() !== 'cli' ) {
			
			if( version_compare( phpversion(), '5.4.0', '>=' ) ) 
				return ( session_status() === PHP_SESSION_ACTIVE );
			else
				return !( session_id() === '' );
			
		}
		
		return false;
	}
	
	
	/**
	 * Charge une session.
	 * Si une session est en cours, l'enregistre avant de la fermer
	 * pour une utilisation future.
	 * Si le nom de la session n'existe pas, alors elle sera créée avec ce nouveau nom.
	 * Si le nom existe alors la session portant ce nom sera réouverte.
	 *
	 * @param	$name		Nom de la session à charger
	 *
	 * @return	boolean		Vrai si la session est chargée, sinon Faux
	 */
	function sessionStart( $name = 'PHPSESSID' ) {
		static $my_sessions = array();
	
		if( isSessionStarted() || session_id() !== '' )
			session_write_close();
			
		session_name( $name );
		
		if( isset( $_COOKIE[ $name ] ) )
			$my_sessions[ $name ] = $_COOKIE[ $name ];
			
		if( isset( $my_sessions[ $name ] ) ) {
			
			session_id( $my_sessions[ $name ] );
			session_start();
			
		} else {
		
			session_start();
			$_SESSION = array();
			
			session_regenerate_id( empty( $my_sessions ) );
			$my_sessions[ $name ] = session_id();
		
		}
		
		return isSessionStarted();
	}




?>
