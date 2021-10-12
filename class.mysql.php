<?php
/**
 * Classe PHP MySql
 *
 * Cette classe très pratique vous permettra de gagner du temps lorsque vous aurez besoin de lancer des requêtes SQL. 
 * En effet, celle-ci a été conçue pour lancer une requête SQL à vitesse grand « V ».
 * De plus, cette classe protège toutes les données des injections SQL, hormis certains paramètres ou variable pour
 * cela je vous invite à lire la documentation.<br>
 *
 * @category   Base de données MySql
 * @author     Filipe gomes <filipe91@ymail.com>
 * @copyright  2011-2012 Filipe Gomes
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Version 1.0
 */
class MySqlClass {

    /**
     * Mettez true pour que les messages d'erreur soit automatiquement affichés sinon mettez false
     * @var boolean 
     */
    public $AfficherMessException = true;
    
    /**
     * Ferme votre connexion automatiquement après chaque requête, par défaut 
     * c'est false, la connexion reste ouverte.  
     * @var boolean 
     */
    public $CloseMysqlAuto = false;
    
    /**
     * Vous permet d'avoir l'aperçu lorsque vous créer une table SQL avec $MysqlBddCreateTable()
     * @var string 
     */
    public $ApercuCreateTableSql='';
    
    /**
     * Stocke le dernier message d'erreur trouvé dans cette classe
     * @var string 
     */
    public $GetErrorMysql = '';
    
    /**
     * Stocke le dernier identifiant d'une requête INSERT
     * @var int 
     */
    public $DernierID = 0;
    
    /**
     * Nom du serveur MySql (en local c'est localhost)
     * @var string
     */
    public $SqlHost = '';
    
    /**
     * Nom utilisateur de la base de données (en local mettez root)
     * @var string
     */
    public $SqlUser = '';
    
    /**
     * Mot de passe d'accès à la base de données (en local, laissez vide)
     * @var string  
     */
    public $SqlPass = '';
    
    /**
     * Nom de la base de données
     * @var string
     */
    public $SqlBdd  = '';
    
    
    
    /**
     * <b>ATTENTION: CETTE ACTION SUPPRIME VOTRE TABLE DE LA BASE DE DONNEES MAIS AUSSI TOUTES LES DONNEES PRESENTES ET SERONT PERDUES DEFFINITIVEMENT.</b><br><br>
     * Supprime une table de votre base de données (ne pas confondre avec la clause DELETE)<br><br>
     * <b>Exemple d'utilisation :</b><code>
     * $MysqlBddDropTable('table_a_supprimer');
     * </code>
     * @param string $table Nom de la table SQL à supprimer
     * @return boolean Retourne TRUE si la suppression c'est bien déroulé 
     */
    public function MysqlBddDropTable($table)
    {
        try {
            // reconnecte automatiquement si $CloseMysqlAuto est TRUE
            if($this->CloseMysqlAuto && mysql_ping()){
                $this->MysqlOpen ($this->SqlHost, $this->SqlUser, $this->SqlPass, $this->SqlBdd);
            }
            
            // on supprime la table SQL
            $requete=mysql_query("DROP TABLE IF EXISTS `".$table."`");
            if (!$requete){
                // s'il y a eu une erreur on arrête le script
                throw new Exception("Requête DROP TABLE invalide : ".mysql_error());
            }
                        
            // on ferme la connexion si $CloseMysqlAuto est TRUE
            if ($this->CloseMysqlAuto) {
                $this->MysqlClose();
            }     
            return true;
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        }        
    }
    
    
    /**
     * Cette méthode vous permet de créer une table SQL.<br><br>
     * <b>Toutes les données dans cette méthode sont protégées contre l'injection SQL</b><br><br>
     * <b>Exemple d'utilisation</b><code>
     * // 1ere colonne
     * $colonnes_sql1 = array(
     *     "NOM"            => "id", // Nom de la colonne (obligatoire)
     *     "TYPE"           => "INT", // Type de colonne (obligatoire)
     *     "TAILLE"         => "11", // Taille de la colonne (obligatoire)
     *     "VALEUR_DEFAUT"  => "", // Valeur par défaut
     *     "NULL"           => "1", // NULL (laissez vide le cas contraire)
     *     "AUTO_INCREMENT" => "1", // Colonne auto incrémentée (laissez vide le cas contraire)
     *     "INDEX"          => "PRIMARY", // Index de la table (un seul index par table)
     *     "COMMENTAIRES"   => "Com" // Commentaire
     * );
     * // 2e colonne
     * $colonnes_sql2 = array("NOM" => "nom_colonne2", "TYPE" => "VARCHAR", "TAILLE" => "255", "VALEUR_DEFAUT" => "Ma valeur par défaut");
     * // 3e colonne
     * $colonnes_sql3 = array("NOM" => "nom_colonne3", "TYPE" => "TEXT", "TAILLE" => "");
     * $MysqlBddCreateTable('ma_nouvelle_table', array($colonnes_sql1, $colonnes_sql2, $colonnes_sql3));
     * </code>
     * @param string $nom_table Nom de la table SQL que vous souhaitez créer
     * @param array $insertion Elements à insérer dans votre table. Les paramètres obligatoires sont : NOM, TYPE et TAILLE
     * @param string $p_engin Moteur de la table. Par défaut c'est InnoDB
     * @param string $p_charset_defaut Charset par défaut, la valeur par défaut est latin1 
     * @return boolean Retourne TRUE si la table a été créée avec succès 
     */
    public function MysqlBddCreateTable($nom_table, $insertion, $p_engin='InnoDB', $p_charset_defaut='latin1')
    {
        try {      
            // reconnecte automatiquement si $CloseMysqlAuto est TRUE
            if($this->CloseMysqlAuto && mysql_ping()){
                $this->MysqlOpen ($this->SqlHost, $this->SqlUser, $this->SqlPass, $this->SqlBdd);
            }
            
            $primarykey_compte=0;
            $primarykey_name='';
            
            if (!is_array($insertion) || count($insertion)<1) {
                throw new Exception("Impossible de créer la table SQL car il manque des éléments dans les paramètres de la fonction "."$"."MysqlBddCreateTable.");
            }
            
            // début de la creation de la table SQL
            $creation = "CREATE TABLE IF NOT EXISTS `".$this->SqlBdd."`.`".$nom_table."` (";
            $apercu   = "CREATE TABLE IF NOT EXISTS `".$nom_table."` (<br />";
            // on ouvre une boucle for pour récupérer toutes les données en paramètres
            for ($i = 0; $i < count($insertion); $i++) {                
                $col = $insertion[$i];
                
                // on vérifie que les paramètres NOM, TYPE et TAILLE soivent bien déclarés
                if (!isset($col['NOM']) || !isset($col['TYPE']) || !isset($col['TAILLE'])) {
                    throw new Exception("Impossible de créer la table SQL car il manque au moins un paramètre \"NOM\", \"TYPE\" ou \"TAILLE\" dans la fonction "."$"."MysqlBddCreateTable.");
                } else if (empty($col['NOM']) || empty($col['TYPE'])) {
                    throw new Exception("Impossible de créer la table SQL car au moins un paramètre \"NOM\", ou \"TYPE\" est vide dans la fonction "."$"."MysqlBddCreateTable.");
                }
                
                // on récupère toutes les données et on les protèges contre l'injection SQL
                $nom    = MySqlProtectVal($col['NOM']);
                $type   = MySqlProtectVal(strtoupper($col['TYPE']));
                $taille = '('.MySqlProtectVal($col['TAILLE']).')';
                // on récupère aussi les paramètres qui ne sont pas obligatoires
                (isset($col['VALEUR_DEFAUT']) && !empty($col['VALEUR_DEFAUT']))? $defaut = " DEFAULT '".MySqlProtectVal($col['VALEUR_DEFAUT'])."'" : $defaut = '';
                (isset($col['NULL']) && !empty($col['NULL']))? $nule = ' NOT NULL' : $nule = ''; 
                (isset($col['AUTO_INCREMENT']) && !empty($col['AUTO_INCREMENT']))? $autoincrement = ' AUTO_INCREMENT' : $autoincrement = '';
                (isset($col['INDEX']) && !empty($col['INDEX']))? $primarykey = ' '.MySqlProtectVal($col['INDEX']) : $primarykey = '';
                (isset($col['COMMENTAIRES']) && !empty($col['COMMENTAIRES']))? $commentaires = ' COMMENT '." '".MySqlProtectVal($col['COMMENTAIRES'])."'" : $commentaires = '';   
                (isset($col['CHARACTER_SET']) && !empty($col['CHARACTER_SET']))? $character_set = ' '.MySqlProtectVal($col['CHARACTER_SET']) : $character_set = '';
                                
                // si la colonne doit-être 'auto increment', il faut supprimer la valeur par défaut car risque d'erreur et NULL doit être NOT NULL
                if (!empty($autoincrement)) $defaut='';
                if (!empty($autoincrement)) $nule = ' NOT NULL';                
                
                // on effectue quelques vérification dans les paramètres
                if ($type == 'TEXT' || $type=='FLOAT' || $type=='DOUBLE' || $type=='DATE' || $type=='DATETIME' || $type=='TIMESTAMP' || $type=='TIME' || $type=='TINYTEXT' || $type=='MEDIUMTEXT' || $type=='LONGTEXT') {
                    // tout ce qui est de type TEXT, DATE, DATETIME etc... n'ont pas de taille minimum ou maximum, donc on vide $taille
                    $taille = '';
                } 
                else if ($type=='INT' || $type=='TINYINT' || $type=='SMALLINT' || $type=='MEDIUMINT' || $type=='BIGINT') {
                    // si auto increment est actif, il faut vider la valeur par défaut, sinon il y aurra une erreur
                    if (!empty($autoincrement) && !empty($defaut)) { $defaut = ''; } 
                    // taille par défaut des paramètres TYPE (numérique) si le paramètre TAILLE est vide
                    if (empty($taille)) { if ($type=='INT') { $taille='(11)'; } else if ($type=='TINYINT') { $taille='(4)'; } else if ($type=='SMALLINT') { $taille='(6)'; } else if ($type=='MEDIUMINT') { $taille='(9)'; } else if ($type=='BIGINT') { $taille='(20)'; } }
                } 
                else if (empty($taille) && ($type=='VARCHAR' || $type=='CHAR'))  {
                    // si la taille VARCHAR et CHAR est vide
                    $taille = '(255)';
                }                
                else if ($type=='YEAR') {
                    // gestion du type YEAR, la taille doit être de 2 ou 4 obligatoirement
                    if ($taille=='2') $taille = '(2)'; else $taille = '(4)';
                }
                else if ($type=='VARCHAR' && $taille > '255') {
                    // si le paramètre TAILLE est supérieur à 255, on arrête le script pour en informer l'utilisateur
                    throw new Exception("Impossible de créer la table SQL car la TAILLE du paramètre VARCHAR ne peut pas dépasser 255. Erreur dans la fonction "."$"."MysqlBddCreateTable.");  
                } 
                else if (!empty($primarykey) && $primarykey!='PRIMARY' && $primarykey!='FULLTEXT' && $primarykey!='INDEX' && $primarykey!='UNIQUE') {
                    // si le paramètre INDEX ne contient pas le bon paramètre on arrête le script pour en informer l'utilisateur
                    throw new Exception("Impossible de créer la table SQL car le paramètre INDEX n'est pas valide. Les paramètres valides sont: PRIMARY, UNIQUE, INDEX ou FULLTEXT. Erreur dans la fonction "."$"."MysqlBddCreateTable.");                    
                }                      
                
                // on stocke notre ligne dans la variable $creation 
                $virgule='';
                if($i<(count($insertion)-1) ) { $virgule.=','; }
                $creation .= "`".$nom."` ".$type.$taille.$character_set.$nule.$autoincrement.$defaut.$commentaires.$virgule; 
                $apercu   .= "&nbsp;&nbsp;&nbsp;&nbsp;`".$nom."` ".$type.$taille.$character_set.$nule.$autoincrement.$defaut.$commentaires.$virgule."<br />";
                if (!empty($primarykey) && $primarykey_compte==0) {
                    if($primarykey=='INDEX'){ $primarykey=''; }
                    $primarykey_compte=1;
                    $primarykey_name=', '.$primarykey.' KEY (`'.$nom.'`)';
                }
            }
            
            if (!empty($primarykey_name)) {
                $creation .= $primarykey_name;
                $apercu   .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$primarykey_name.'<br />';
            }
            $creation .= ") ENGINE=".$p_engin." DEFAULT CHARSET=".$p_charset_defaut;
            $apercu   .= ") ENGINE=".$p_engin." DEFAULT CHARSET=".$p_charset_defaut."<br />";
            
            // on stocke l'apercu dans la variable $ApercuCreateTableSql
            $this->ApercuCreateTableSql = $apercu;    
            
            // création de la table dans la base de données
            $requete=mysql_query($creation);
            if (!$requete){
                // s'il y a eu une erreur on arrête le script
                throw new Exception("Requête CREATE TABLE invalide : ".mysql_error());
            }   
            
            // on ferme la connexion si $CloseMysqlAuto est TRUE
            if ($this->CloseMysqlAuto) {
                $this->MysqlClose();
            }            
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        }
        return true;
    }
    
    
    /**
     * L'instruction SELECT est utilisée pour sélectionner des données à partir d'une base de données.<br><br>
     * <b>Exemple 1</b> (avec boucle WHILE)<code>
     * $req = $bdd->MysqlSelect('matable', '*', 'ORDER BY id DESC'); 
     * for ($i=0;$i<count($req);$i++) {
     *     $ligne = $req[$i];            
     *     echo '
     *     <div>'.$ligne['id'].'</div>
     *     <div>'.$ligne['Colonne2'].'</div>
     *     <div>'.$ligne['Colonne3'].'</div>';           
     * }
     * </code><br>
     * <b>Exemple 2</b> (avec boucle WHILE)<code>
     * $req = $bdd->MysqlSelect('matable', '*', 'ORDER BY id DESC'); 
     * for ($i=0;$i<count($req);$i++) {           
     *     echo '
     *     <div>'.$ligne[$i]['id'].'</div>
     *     <div>'.$ligne[$i]['Colonne2'].'</div>
     *     <div>'.$ligne[$i]['Colonne3'].'</div>';           
     * }
     * </code><br>
     * <b>Exemple 3</b> (sans boucle WHILE)<code>
     * $ligne = $bdd->MysqlSelect('matable', 'id,Colonne3', 'WHERE id=9', false); 
     * echo '<div>Mon ID est '.$ligne['id'].' et ma colonne, '.$ligne['Colonne3'].'</div>'; 
     *  </code>
     * @param string $table Nom de la table de votre base de données
     * @param string $colonne Noms des colonnes à sélectionner ou à afficher
     * @param type $clauses Clauses au format string ou array. Ce paramètre vous permet d'y mettre vos clauses WHERE, ORDER, LIMIT etc...<br><b>(ce paramètre n'est pas protégé contre l'injection SQL)</b>
     * @param boolean $avec_while Si vous ne voulez pas que cette méthode utilise la boucle WHILE mettez false, par défaut ce paramètre est TRUE
     * @return array 
     */
    public function MysqlSelect($table, $colonne='*', $clauses='', $avec_while=true)
    {
        try {     
            // reconnecte automatiquement si $CloseMysqlAuto est TRUE
            if($this->CloseMysqlAuto && mysql_ping()){
                $this->MysqlOpen ($this->SqlHost, $this->SqlUser, $this->SqlPass, $this->SqlBdd);
            }
            
            // si la variable $colonne est vide, on lui met par défaut un *
            if(empty($colonne)) { $colonne='*'; }
            
            $requete="SELECT ".$colonne." FROM `".$table."`".$clauses;            
            $resultat = $this->SelectBase($requete, $avec_while);
            
            // on ferme la connexion si $CloseMysqlAuto est TRUE
            if ($this->CloseMysqlAuto) {
                $this->MysqlClose();
            }
            
            return $resultat;
            
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        }       
    }
    
    /**
     * L'instruction SELECT est utilisée pour sélectionner des données à partir d'une base de données.<br><br>
     * <b>Exemple</b><code>
     * $req = $bdd->MysqlSelect("SELECT * FROM table WHERE Colonne1='Valeur' ORDER BY id"); 
     * for ($i=0;$i<count($req);$i++) {
     *     $ligne = $req[$i];            
     *     echo '
     *     <div>'.$ligne['id'].'</div>
     *     <div>'.$ligne['Colonne2'].'</div>
     *     <div>'.$ligne['Colonne3'].'</div>';           
     * }
     * </code><br>
     * @param string $ligneSql Ligne complète de votre requête SQL
     * @param boolean $avec_while Si vous ne voulez pas que cette méthode utilise la boucle WHILE mettez false, par défaut ce paramètre est TRUE
     * @return array 
     */
    public function MysqlSelectPerso($ligneSql, $avec_while=true)
    {
        try {     
            // reconnecte automatiquement si $CloseMysqlAuto est TRUE
            if($this->CloseMysqlAuto && mysql_ping()){
                $this->MysqlOpen ($this->SqlHost, $this->SqlUser, $this->SqlPass, $this->SqlBdd);
            }
                       
            $requete=$ligneSql;            
            $resultat = $this->SelectBase($requete, $avec_while);
            
            // on ferme la connexion si $CloseMysqlAuto est TRUE
            if ($this->CloseMysqlAuto) {
                $this->MysqlClose();
            }
            
            return $resultat;
            
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        }       
    }
    
    /**
     * Pour les méthodes MysqlSelect et MysqlSelectPerso<br>
     * @param string $ligneSql
     * @param boolean $avec_while
     * @return array 
     */
    private function SelectBase($ligneSql, $avec_while)
    {
        try {
            // déclaration des variables
            $tab=array();
            
            // ouverture de la requête
            $req = mysql_query($ligneSql);
            if(!$req) {
                // s'il y a eu une erreur on arrête le script
                throw new Exception("Requête SELECT invalide : ".mysql_error());
            }            
            // on récupère le nombre de colonne disponible dans la table SQL
            $NbColonnes = mysql_num_fields($req);

            // si $avec_while est égal à true, on utilisera une boucle WHILE
            if ($avec_while)
            {
                // Déclaration d'une variable qui servira d'index pour le tableau ($tab)
                $NumIndex=0;
                // ouverture d'une boucle pour récupèrer toutes les valeurs de la BDD demandées
                while($temp = mysql_fetch_array($req)) 
                {           
                    // ouverture d'une boucle pour récupèrer les noms de toutes les colonnes
                    for ($i=0; $i < $NbColonnes; $i++) 
                    {
                        // on stocke le nom de la colonne dans une variable
                        $NomColonne = mysql_field_name($req, $i);
                        // on prépare notre tableau final
                        $tab[$NumIndex][$NomColonne] = $temp[$NomColonne];
                    }
                    // indexation de l'index du tableau ($tab)
                    $NumIndex++;
                }               
            }
            // autrement si $avec_while est égal à false...
            else 
            {
                $temp = mysql_fetch_array($req);
                // ouverture d'une boucle pour récupèrer les noms de toutes les colonnes
                for ($i=0; $i < $NbColonnes; $i++) 
                {
                    // on stocke le nom de la colonne dans une variable
                    $NomColonne = mysql_field_name($req, $i);
                    // on prépare notre tableau final
                    $tab[$NomColonne] = $temp[$NomColonne];
                }                
            }           

            // on libère notre requête SQL
            mysql_free_result($req);
            
            // on retourne le tableau
            return $tab;
            
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        } 
    }    
    
    
    /**
     * <b>Toutes les valeurs mise à jour dans cette méthode sont protégés contre l'injection SQL. Seule le paramètre $clauses n'est pas protégé.</b><br><br>
     * <b>Exemple 1</b><code>
     * $MysqlDelete("matable", "WHERE id=7");
     * </code>
     * <b>Exemple 2</b><code>
     * $MysqlDelete("matable", array(array("WHERE" => "id=7")));
     * </code>
     * @param string $table Nom de la table de votre base de données
     * @param type $clauses Clauses au format string ou array. Ce paramètre vous permet d'y mettre vos clauses WHERE, ORDER, LIMIT etc...<br><b>(ce paramètre n'est pas protégé contre l'injection SQL)</b>
     * @return boolean 
     */
    public function MysqlDelete($table, $clauses='')
    {
        try {          
            // reconnecte automatiquement si $CloseMysqlAuto est TRUE
            if($this->CloseMysqlAuto && mysql_ping()){
                $this->MysqlOpen ($this->SqlHost, $this->SqlUser, $this->SqlPass, $this->SqlBdd);
            }
            
            // on prépare la requête SQL            
            $requete = mysql_query("DELETE FROM `".$table."`".$this->Clauses($clauses));
            // si une erreur est arrivée, on arrête le script pour générer une erreur
            if (!$requete) {
                // s'il y a eu une erreur on arrête le script
                throw new Exception("Requête DELETE invalide : ".mysql_error());
            } 
            // on ferme la connexion si $CloseMysqlAuto est TRUE
            if ($this->CloseMysqlAuto) {
                $this->MysqlClose();
            }
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        }
        return true;
    }
    
    /**
     * Permet de mettre à jour des enregistrements existants dans une table<br /><br />
     * <b>Toutes les valeurs mise à jour dans cette méthode sont protégés contre l'injection SQL. Seule le paramètre $clauses n'est pas protégé.</b><br><br>
     * <b>Exemple 1</b><code>
     * $MysqlUpdate("matable", 
     *     array (            
     *         "Colonne1" => "Nouvelle valeur",
     *         "Colonne2" => "Nouvelle valeur"            
     *     ), 
     *     "WHERE id=7"
     * );
     * </code>
     * <b>Exemple 2</b><code>
     * $MysqlUpdate("matable", 
     *     array(            
     *          "Colonne1" => "Nouvelle valeur",
     *          "Colonne3" => "Nouvelle valeur"            
     *      ), 
     *      array(
     *          array("WHERE" => "id=7")
     *      )
     *  );
     * </code>
     * @param string $table Nom de la table de votre base de données
     * @param array $mise_a_jour Nom des colonnes et valeurs à mettre à jour
     * @param type $clauses Clauses au format string ou array. Ce paramètre vous permet d'y mettre vos clauses WHERE, ORDER, LIMIT etc...<br><b>(ce paramètre n'est pas protégé contre l'injection SQL)</b>
     * @return boolean 
     */
    public function MysqlUpdate($table, $mise_a_jour, $clauses='')
    {
        try {           
            // reconnecte automatiquement si $CloseMysqlAuto est TRUE
            if($this->CloseMysqlAuto && mysql_ping()){
                $this->MysqlOpen ($this->SqlHost, $this->SqlUser, $this->SqlPass, $this->SqlBdd);
            }
            
            $maj='';           
            // on récupère les valeurs à mettre à jour dans un tableau
            foreach ($mise_a_jour as $cle => $valeur) {                
                $maj[] = "`".$cle."`"."='".MySqlProtectVal($valeur)."'";                
            }           
            // on prépare la requête SQL            
            $requete = mysql_query("UPDATE `".$table."` SET ".implode(',',$maj)."  ".$this->Clauses($clauses));
            
            // si une erreur est arrivée, on arrête le script pour générer une erreur
            if (!$requete) {
                // s'il y a eu une erreur on arrête le script
                throw new Exception("Requête UPDATE invalide : ".mysql_error());
            }      
            
            // on ferme la connexion si $CloseMysqlAuto est TRUE
            if ($this->CloseMysqlAuto) {
                $this->MysqlClose();
            }
            
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        }
        return true;
    }   
    
    /**
     * Retourne le nombre de lignes qui correspond à un critère spécifique
     * 
     * <b>Exemple 1</b><code>
     * echo $bdd->MysqlCount('matable', 'id', "WHERE Colonne1='Valeur' AND Colonne1='Valeur2'");
     * </code>      
     * <b>Exemple 2</b><code>
     * echo $bdd->MysqlCount('matable', 'id', array(
     *     array("WHERE" => "Colonne1='Valeur' AND Colonne1='Valeur2'")
     * ));
     * </code>
     * 
     * @param string $table Nom de la table de votre base de données
     * @param string $compter Nom de la colonne à compter, par défaut c'est * (tout)
     * @param type $clauses Clauses au format string ou array. Ce paramètre vous permet d'y mettre vos clauses WHERE, ORDER, LIMIT etc...<br><b>(ce paramètre n'est pas protégé contre l'injection SQL)</b>
     * @return int 
     */
    public function MysqlCount($table, $compter='*', $clauses='')
    {
        $resultat = 0;
        try {   
            // reconnecte automatiquement si $CloseMysqlAuto est TRUE
            if($this->CloseMysqlAuto && mysql_ping()){
                $this->MysqlOpen ($this->SqlHost, $this->SqlUser, $this->SqlPass, $this->SqlBdd);
            }
            
            if(empty($compter)) $compter='*';
            // on lance la requête
            $requete = mysql_query("SELECT COUNT(".$compter.") FROM `".$table."`".$this->Clauses($clauses));
            if(!$requete) {       
                // s'il y a eu une erreur on arrête le script
                throw new Exception("Requête COUNT invalide : ".mysql_error());
            }
            // on récupère le résultat de notre requête
            $resultat = mysql_fetch_row($requete);
            // on libère la requête
            mysql_free_result($requete);
            
            // on ferme la connexion si $CloseMysqlAuto est TRUE
            if ($this->CloseMysqlAuto) {
                $this->MysqlClose();
            }
            
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        }
        return $resultat[0];
    }   
    
    /**
     * Cette méthode permet d'insérer de nouveaux enregistrements dans un tableau<br><br>
     * <b>Toutes les valeurs dans cette méthode sont protégés contre l'injection SQL</b><br><br>
     * <b>Exemple d'utilisation :</b><code>
     * $MysqlInsert('matable', array(
     *     "Colonne1" => "Valeur",
     *     "Colonne2" => "Valeur",
     *     "Colonne3" => "Valeur"
     * ));
     * </code>
     * @param string $table Nom de la table de la base de données
     * @param array $insertion Elements à insérer dans la base de données
     * @return boolean 
     */
    public function MysqlInsert($table, $insertion)
    {   
        try {
            // reconnecte automatiquement si $CloseMysqlAuto est TRUE
            if($this->CloseMysqlAuto && mysql_ping()){
                $this->MysqlOpen ($this->SqlHost, $this->SqlUser, $this->SqlPass, $this->SqlBdd);
            }
            
            // on récupère les valeurs, on utilisera array_map() pour inclure la fonction
            // MySqlProtectVal et ainsi protéger les données contre l'injection SQL
            $valeurs = array_map('MySqlProtectVal', array_values($insertion));
            // on récupère les clefs du tableau
            $clefs = array_keys($insertion);
            // insertion des éléments dans la base de données
            $requete=mysql_query("INSERT INTO `".$table."` (`".implode("`,`",$clefs)."`) VALUES ('".implode("','",$valeurs)."')");
            if(!$requete) {                
                throw new Exception("Requête INSERT invalide : ".mysql_error());
            }
            // on récupère l'identifiant de la requete ajouté
            $this->DernierID = mysql_insert_id();
            
            // on ferme la connexion si $CloseMysqlAuto est TRUE
            if ($this->CloseMysqlAuto) {
                $this->MysqlClose();
            }
            
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        }        
        return true; 
    }    
    
    /**
     * Gestion des clauses pour la base de données
     * @param type $clauses Clauses au format string ou array. Ce paramètre vous permet d'y mettre vos clauses WHERE, ORDER, LIMIT etc...<br><b>(ce paramètre n'est pas protégé contre l'injection SQL)</b>
     * @return string 
     */
    private function Clauses($clauses)
    {
        $resultat = '';
        $temp='';
        
        try {            
            if (is_array($clauses) && count($clauses)>0)
            {  
                for ($i=0; $i<count($clauses); $i++)
                {                
                    foreach ($clauses[$i] as $clef => $valeur) 
                    {
                        // on force la clef a être en majuscule
                        $clef = strtoupper($clef);
                        
                        // LEFT JOIN
                        if ($clef=='LEFT JOIN' || $clef=='LEFTJOIN' || $clef=='LEFT_JOIN' || $clef=='LJ' || $clef=='LEFT') {
                            $temp .= ' LEFT JOIN '.trim($valeur);
                        }
                        // RIGHT JOIN
                        else if ($clef=='RIGHT JOIN' || $clef=='RIGHTJOIN' || $clef=='RIGHT_JOIN' || $clef=='LR' || $clef=='RIGHT') {
                            $temp .= ' RIGHT JOIN '.trim($valeur);
                        }
                        // WHERE
                        else if ($clef=='WHERE' || $clef=='W') {
                            $temp .= ' WHERE '.trim($valeur);
                        }
                        // LIKE
                        else if ($clef=='LIKE' || $clef=='WL' || $clef=='WHERE LIKE' || $clef=='WHERELIKE' || $clef=='WHERE_LIKE') {
                            $temp .= ' WHERE LIKE '.trim($valeur);
                        }
                        // ORDER BY
                        else if ($clef=='ORDER BY' || $clef=='ORDERBY' || $clef=='ORDER_BY' || $clef=='ORDER' || $clef=='O') {
                            $temp .= ' ORDER BY '.trim($valeur);
                        }
                        // GROUP BY
                        else if ($clef=='GROUP BY' || $clef=='GROUPBY' || $clef=='GROUP_BY' || $clef=='GROUP' || $clef=='G') {
                            $temp .= ' GROUP BY '.trim($valeur);
                        }
                        // HAVING
                        else if ($clef=='HAVING' || $clef=='H') {
                            $temp .= ' HAVING '.trim($valeur);
                        }
                        // LIMIT
                        else if ($clef=='LIMIT' || $clef=='L') {
                            $temp .= ' LIMIT '.trim($valeur);
                        } 
                        // Autres type de clause
                        else {
                            $temp .= ' '.trim($valeur);
                        }
                    }               
                }                
                $resultat = $temp;
            }
            else
            {
                $resultat = ' '.trim($clauses);
            }
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        }       
        return $resultat;
    }
    
    
    /**
     * <p>Connexion à la base de données</p>
     * @param string $BddHost Nom du serveur MySql (en local c'est localhost)
     * @param string $BddUser Nom utilisateur (en local mettez root)
     * @param string $BddPass Mot de passe (en local, laissez vide)
     * @param string $BddName Nom de la base de données
     * @return boolean Retourne TRUE si la connexion c'est déroulée avec succès
     */
    public function MysqlOpen($BddHost='', $BddUser='', $BddPass='', $BddName='')
    {
        $resultat = true;       
        try {            
            // si l'utilisateur donne les informations de connexion dans les paramètres de cette méthode
            if(!empty($BddHost) && !empty($BddName) && !empty($BddUser))
            {
                // on stocke les données de connexion dans les variables
                $this->SqlHost = $BddHost;
                $this->SqlUser = $BddUser;
                $this->SqlPass = $BddPass;
                $this->SqlBdd  = $BddName;
            }
            
            // si l'utilisateur n'a donné aucune information de connexion
            if(empty($this->SqlBdd) && empty($this->SqlHost) && empty($this->SqlUser))
            {
                // on arrête le script pour afficher un message d'erreur
                throw new Exception("Vous devez indiquer vos informations de connexion");
            }
            
            // Ouverture de la connexion au serveur MySQL 
            if (!@mysql_connect($this->SqlHost, $this->SqlUser, $this->SqlPass)) {
                $resultat = false;
                // en cas d'erreur on arrête le script
                throw new Exception("Erreur de connexion au serveur ".$this->SqlHost);                      
            }            
            // Sélection de la base de données MySql
            if ($resultat && !@mysql_select_db($this->SqlBdd)) {
                // en cas d'erreur on arrête le script
                throw new Exception("Erreur de connexion à la base de données"); 
            }            
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        }        
        return $resultat;
    }    
    
    /**
     * Met fin à la connection
     * @return boolean
     */
    public function MysqlClose()
    {
        mysql_close();
        return true;
    }
    
    /**
     * Gère les messages d'erreur sur un try catch
     * @param string $message Message d'erreur
     * @return boolean 
     */
    private function GestionException($message)
    {        
        $this->GetErrorMysql = $message;            
        if($this->AfficherMessException) echo '<p>'.$message.'</p>';
        // on ferme la connexion si $CloseMysqlAuto est TRUE
        if ($this->CloseMysqlAuto) {
            $this->MysqlClose();
        }
        return false;
    }    
    
}
/**
 * Cette méthode protège la base de données contre les injections SQL<br>
 * @param string $chaine Chaine à protéger
 * @return string 
 */
function MySqlProtectVal($chaine)
{
    // SI VOUS VOULEZ MODIFIER CETTE FONCTION, VOUS DEVEZ PRENDRE CERTAINES PRECAUTIONS:
    // 1 - NE PAS DEPLACER CETTE FONCTION, ELLE DOIT RESTER OU ELLE EST
    // 2 - NE PAS MODIFIER LE NOM DE CETTE FONCTION

    // on supprime les caractères invisibles en début et fin de chaîne
    $chaine = trim($chaine);

    // si magic quote est activé on ajoute des slashes
    if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) 
        $chaine = stripslashes($chaine);

    // si la fonction mysql_real_escape_string existe on l'utilise plutôt que addslashes qui est moins performant
    if (function_exists('mysql_real_escape_string')) 
        $chaine = mysql_real_escape_string($chaine);
    else 
        $chaine = addslashes($chaine);    

    return $chaine; // on retourne le résultat final     
}
?>