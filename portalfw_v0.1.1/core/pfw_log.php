<?php
	/**
	* PortalFW - Framework PHP pour la conception d'un portail personnel
	*
	* /core/pfw_log.php
	*
	* Gestion des logs
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
	 * Gestion des Logs
	 */
	class Log {

	
		// Nombre de lignes maximum par fichier Log
		const MAX_LINES 	= 1000;
		 
		// Types de logs admissibles
		const GRANT_VOID	= 0;	// Général
		const GRANT_YEAR	= 1;	// Classement par années
		const GRANT_MONTH	= 2;	// Classement par mois
		const GRANT_DAY		= 3;	// Classement par jours
		
	
		private $directory = './';
		
		
		/**
		 * Traduction du message Log.
		 * Peut remplacer la fonction de traduction du module locale si celui-ci n'est pas chargé.
		 *
		 * @param	$message	Message à traduire
		 *
		 * @return	string		Message traduit
		 */
		private function e( $message ) {
			if( function_exists( '__' ) && is_callable( '__' ) )
				return call_user_func( '__', $message );
				
			return _( $message );
		}
		
		
		/**
		 * Point d'entrée de la classe Log.
		 * Renseigne en paramètre le dossier ou sont stockés les Logs
		 *
		 * @param	$directory	Chemin complet du dossier contenant les logs
		 */
		public function __construct( $directory ) {

			$path = realpath( $directory );
			if( $path === false || !is_dir( $path ) ) {
				
				if( !@mkdir( $directory, 0755 ) )
					throw new Exception( sprintf( $this->e( "Unable to create log directory '%s'" ), $directory ) );
					
			}
			
			$this->directory = $path;
		
		}
		
		
		/**
		 * Inscrit un nouveau log
		 *
		 * @param	$type		Type de log définit manuelement
		 * @param	$grant		Constante parmis GRANT_VOID, GRANT_DAY, GRANT_MONTH, GRANT_YEAR
		 * @param	$category	Catégorie pour les classements
		 * @param	$message	Message à inscrire
		 * @param	$file		Nom du fichier concerné ( __FILE__ utilisé communement )
		 * @param	$linenum	Ligne du fichier concerné ( __LINE__ utilisé communement )
		 *
		 * @return	void
		 */
		private function log( $type, $grant, $category, $message, $file = null, $linenum = null ) {

			$path = $this->directory . '/' . trim( $type );
			
			if( !file_exists( $path ) ) {
			
				if( !mkdir( $path, 0755 ) )
					throw new Exception( sprintf( $this->e( "Unable to create log type directory '%s'" ), $path ) );
					
			}
			
			if( $grant != self::GRANT_VOID ) {
				
				$path .= '/' . gmdate( 'Y' );
				if( !file_exists( $path ) ) {
			
					if( !@mkdir( $path, 0755 ) )
						throw new Exception( sprintf( $this->e( "Unable to create log year directory '%s'" ), $path ) );
						
				}
				
				if( $grant == self::GRANT_MONTH || $grant == self::GRANT_DAY ) {
				
					$path .= '/' . gmdate( 'm' );
					if( !file_exists( $path ) ) {
				
						if( !@mkdir( $path, 0755 ) )
							throw new Exception( sprintf( $this->e( "Unable to create log month directory '%s'" ), $path ) );
							
					}
					
				}
				
				if( $grant == self::GRANT_DAY ) {
				
					$path .= '/' . gmdate( 'd' );
					if( !file_exists( $path ) ) {
				
						if( !@mkdir( $path, 0755 ) )
							throw new Exception( sprintf( $this->e( "Unable to create log day directory '%s'" ), $path ) );
							
					}
					
				}
				
			}
			
			$path .= '/' . strtolower( trim( $category ) ) . '.log';

			
			$line = sprintf( "[%s]\t[%s GMT]\t%s", strtoupper( trim( $type ) ), gmdate( 'Y/m/d H:i:s' ), $message );
			if( !is_null( $linenum ) )
				$line .= ' | ' . $this->e( 'line' ) . ' ' . $linenum;
			if( !is_null( $file ) )
				$line .= ' | ' . $file;
			
		
			$content = array();
			if( file_exists( $path ) ) {
			
				$content = trim( file_get_contents( $path ) );
				if( $content == '' ) {
					
					$content = array();
					
				} else {
				
					$content = explode( PHP_EOL, $content );
					if( count( $content ) >= self::MAX_LINES )
						$content = array_slice( $content, 0, self::MAX_LINES - 1 );
					
				}
			}
			array_unshift( $content, $line );
			$content = implode( PHP_EOL, $content );
			
			$f = @fopen( $path, 'w' );
			if( $f === false )
				return;
				
			fwrite( $f, $content );
			fclose( $f );
			
		}
		
		
		/**
		 * Inscrit un nouveau log - Categorie DEBUG
		 *
		 * @param	$category	Catégorie pour les classements
		 * @param	$message	Message à inscrire
		 * @param	$grant		Constante parmis GRANT_VOID, GRANT_DAY, GRANT_MONTH, GRANT_YEAR
		 * @param	$file		Nom du fichier concerné ( __FILE__ utilisé communement )
		 * @param	$linenum	Ligne du fichier concerné ( __LINE__ utilisé communement )
		 *
		 * @return	void
		 */
		public function debug( $category, $message, $grant = self::GRANT_VOID, $file = null, $linenum = null ) {
			
			$this->log( 'debug', $grant, $category, $message, $file, $linenum );
			
		}
		
		
		/**
		 * Inscrit un nouveau log - Categorie INFO
		 *
		 * @param	$category	Catégorie pour les classements
		 * @param	$message	Message à inscrire
		 * @param	$grant		Constante parmis GRANT_VOID, GRANT_DAY, GRANT_MONTH, GRANT_YEAR
		 * @param	$file		Nom du fichier concerné ( __FILE__ utilisé communement )
		 * @param	$linenum	Ligne du fichier concerné ( __LINE__ utilisé communement )
		 *
		 * @return	void
		 */
		public function info( $category, $message, $grant = self::GRANT_VOID, $file = null, $linenum = null ) {
			
			$this->log( 'info', $grant, $category, $message, $file, $linenum );
			
		}
		
		
		/**
		 * Inscrit un nouveau log - Categorie WARNING
		 *
		 * @param	$category	Catégorie pour les classements
		 * @param	$message	Message à inscrire
		 * @param	$grant		Constante parmis GRANT_VOID, GRANT_DAY, GRANT_MONTH, GRANT_YEAR
		 * @param	$file		Nom du fichier concerné ( __FILE__ utilisé communement )
		 * @param	$linenum	Ligne du fichier concerné ( __LINE__ utilisé communement )
		 *
		 * @return	void
		 */
		public function warning( $category, $message, $grant = self::GRANT_VOID, $file = null, $linenum = null ) {
			
			$this->log( 'warning', $grant, $category, $message, $file, $linenum );
			
		}
		
		
		/**
		 * Inscrit un nouveau log - Categorie ERROR
		 *
		 * @param	$category	Catégorie pour les classements
		 * @param	$message	Message à inscrire
		 * @param	$grant		Constante parmis GRANT_VOID, GRANT_DAY, GRANT_MONTH, GRANT_YEAR
		 * @param	$file		Nom du fichier concerné ( __FILE__ utilisé communement )
		 * @param	$linenum	Ligne du fichier concerné ( __LINE__ utilisé communement )
		 *
		 * @return	void
		 */
		public function error( $category, $message, $grant = self::GRANT_VOID, $file = null, $linenum = null ) {
			
			$this->log( 'error', $grant, $category, $message, $file, $linenum );
			
		}
		
		
		/**
		 * Inscrit un nouveau log - Categorie CRITICAL
		 *
		 * @param	$category	Catégorie pour les classements
		 * @param	$message	Message à inscrire
		 * @param	$grant		Constante parmis GRANT_VOID, GRANT_DAY, GRANT_MONTH, GRANT_YEAR
		 * @param	$file		Nom du fichier concerné ( __FILE__ utilisé communement )
		 * @param	$linenum	Ligne du fichier concerné ( __LINE__ utilisé communement )
		 *
		 * @return	void
		 */
		public function critical( $category, $message, $grant = self::GRANT_VOID, $file = null, $linenum = null ) {
			
			$this->log( 'critical', $grant, $category, $message, $file, $linenum );
			
		}






	}

?>
