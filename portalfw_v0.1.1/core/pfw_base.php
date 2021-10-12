<?php
	/**
	* PortalFW - Framework PHP pour la conception d'un portail personnel
	*
	* /core/pfw_base.php
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

	
	// Définit le chemin absolue du site
	define( 'ABSPATH', dirname( dirname( __FILE__ ) ) . '/' );

	
	// Inclut les fonctions communes
	require_once( ABSPATH . 'core/pfw_functions.php' );
	
	
	// Créé les règles minimum dans le fichier .htaccess
	if( !file_exists( ABSPATH . '.htaccess' ) ) {
	
		$hta = 'Options -Indexes' . PHP_EOL . PHP_EOL;
		$hta .= '<FilesMatch "\.(htaccess|ini|inc\.php)$">' . PHP_EOL;
		$hta .= "\t" . 'Deny from all' . PHP_EOL;
		$hta .= '</FilesMatch>' . PHP_EOL;
		
		file_put_contents( ABSPATH . '.htaccess', $hta );
		
		chmod( ABSPATH . '.htaccess', 0600 );
		
	}
	
	
	// Charge les modules du tableau ci dessous
	foreach( array( 'urlweb',
					'locale',
					'geo',
					'ini',
					'log',
					'sessions',
					'sessvars' ) as $module ) {
					
		require_once( ABSPATH . 'core/pfw_' . $module . '.php' );
		
	}

	
?>
