<?php
	/**
	* PortalFW - Framework PHP pour la conception d'un portail personnel
	*
	* /core/pfw_ini.php
	*
	* Lecture et écriture d'un fichier INI
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
	 * Gestion du fichier de configuration sous format INI
	 */
	class Ini {
		
		private $file = '';
		public $datas = array();
	
	
		/**
		 * Point d'entrée de la classe Ini.
		 * Charge le fichier de configuration passé en paramètre, ou le créé s'il n'éxiste pas
		 *
		 * @param	$file	Chemin complet du fichier de configurations
		 */
		public function __construct( $file ) {
			$this->file = $file;
			
			if( file_exists( $this->file ) 
				&& is_readable( $file ) ) {
				
				$parsed = parse_ini_file( $this->file, true, INI_SCANNER_TYPED );
				if( is_array( $parsed ) )
					$this->datas = $parsed;
				
			}
		}
		
		
		/**
		 * Enregistre les modifications
		 *
		 * @return	boolean	Vrai si l'enregistrement a bien été effectué, sinon Faux
		 */
		public function Save() {
			$content = '';
			
			if( is_array( $this->datas ) ) {
				foreach( $this->datas as $section => $values ) {
					
					$content .= '[' . $section . ']' . PHP_EOL;
					
					foreach( $values as $key => $value ) {
						
						if( is_array( $value ) ) {
						
							foreach( $value as $v) {
								$content .= trim( $key . '[] = ' . ( is_numeric( $v ) ? $v : '"' . $v . '"' ) ) . PHP_EOL;
							}
							
						} elseif( is_bool( $value ) ) {
						
							$content .= $key . ' = "' . ( $value ? 'true' : 'false' ) . '"' . PHP_EOL;
							
						} elseif( empty( $value ) ) {
						
							$content .= $key . ' = ' . PHP_EOL;
							
						} else {
						
							$content .= trim( $key . ' = ' . ( is_numeric( $value ) ? $value : '"' . $value . '"' ) ) . PHP_EOL;
						
						}
						
					}
					
					$content .= PHP_EOL;
					
				}			
			}
			
			try {
			
				$handle = @fopen( $this->file, 'w' );
				@fwrite( $handle, trim( $content ) );
				@fclose( $handle );
				
				chmod( $this->file, 0600 );
				
				return true;
			
			} catch( Exception $e ) {
				
				return false;
				
			}
		}

	}	
	
?>
