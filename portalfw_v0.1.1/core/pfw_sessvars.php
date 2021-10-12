<?php
	/**
	* PortalFW - Framework PHP pour la conception d'un portail personnel
	*
	* /core/pfw_sessvars.php
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
	 * Renvoie le contenu d'une variable de session
	 *
	 * @param	$name		Nom de la variable
	 * @param	$default	Valeur par defaut si aucune valeur n'est trouvée
	 *
	 * @return	mixed		Contenu de la variable
	 */
	function svGet( $name, $default = null ) {
		if( isset( $_SESSION ) && isset( $_SESSION[ '|sv_' . $name ] ) )
			return $_SESSION[ '|sv_' . $name ];
		
		return $default;
	}
	
	
	/**
	 * Définit le contenu d'une variable de session
	 *
	 * @param	$name		Nom de la variable
	 * @param	$value		Valeur à enregistrer
	 *
	 * @return	boolean		Vrai si l'enregistrement est effectué sinon Faux
	 */
	function svSet( $name, $value ) {
		if( isset( $_SESSION ) ) {
		
			$_SESSION[ '|sv_' . $name ] = $value;
			
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Supprime une variable de session
	 *
	 * @param	$name		Nom de la variable à supprimer
	 *
	 * @return	boolean		Vrai si la variable est supprimée, sinon Faux
	 */
	function svDel( $name ) {
		if( isset( $_SESSION ) && isset( $_SESSION[ '|sv_' . $name ] ) ) {
		
			$_SESSION[ '|sv_' . $name ] = null;
			unset( $_SESSION[ '|sv_' . $name ] );

			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Supprime toutes les variables de session
	 *
	 * @return	void
	 */
	function svPurge() {
		if( isset( $_SESSION ) ) {
		
			$l = [];
			foreach( $_SESSION as $key => $value ) {
				
				if( strlen( $key ) >= 2 && substr( $key, 0, 4 ) == '|sv_' )
					$l[] = $key;
				
			}
			
			foreach( $l as $k ) {
			
				$_SESSION[ $k ] = null;
				unset( $_SESSION[ $k ] );
				
			}
			
			unset( $l );
		}
	}
	
	
	
	
?>
