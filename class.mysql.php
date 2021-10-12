<?php
/**
 * Classe PHP MySql
 *
 * Cette classe tr�s pratique vous permettra de gagner du temps lorsque vous aurez besoin de lancer des requ�tes SQL. 
 * En effet, celle-ci a �t� con�ue pour lancer une requ�te SQL � vitesse grand � V �.
 * De plus, cette classe prot�ge toutes les donn�es des injections SQL, hormis certains param�tres ou variable pour
 * cela je vous invite � lire la documentation.<br>
 *
 * @category   Base de donn�es MySql
 * @author     Filipe gomes <filipe91@ymail.com>
 * @copyright  2011-2012 Filipe Gomes
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Version 1.0
 */
class MySqlClass {

    /**
     * Mettez true pour que les messages d'erreur soit automatiquement affich�s sinon mettez false
     * @var boolean 
     */
    public $AfficherMessException = true;
    
    /**
     * Ferme votre connexion automatiquement apr�s chaque requ�te, par d�faut 
     * c'est false, la connexion reste ouverte.  
     * @var boolean 
     */
    public $CloseMysqlAuto = false;
    
    /**
     * Vous permet d'avoir l'aper�u lorsque vous cr�er une table SQL avec $MysqlBddCreateTable()
     * @var string 
     */
    public $ApercuCreateTableSql='';
    
    /**
     * Stocke le dernier message d'erreur trouv� dans cette classe
     * @var string 
     */
    public $GetErrorMysql = '';
    
    /**
     * Stocke le dernier identifiant d'une requ�te INSERT
     * @var int 
     */
    public $DernierID = 0;
    
    /**
     * Nom du serveur MySql (en local c'est localhost)
     * @var string
     */
    public $SqlHost = '';
    
    /**
     * Nom utilisateur de la base de donn�es (en local mettez root)
     * @var string
     */
    public $SqlUser = '';
    
    /**
     * Mot de passe d'acc�s � la base de donn�es (en local, laissez vide)
     * @var string  
     */
    public $SqlPass = '';
    
    /**
     * Nom de la base de donn�es
     * @var string
     */
    public $SqlBdd  = '';
    
    
    
    /**
     * <b>ATTENTION: CETTE ACTION SUPPRIME VOTRE TABLE DE LA BASE DE DONNEES MAIS AUSSI TOUTES LES DONNEES PRESENTES ET SERONT PERDUES DEFFINITIVEMENT.</b><br><br>
     * Supprime une table de votre base de donn�es (ne pas confondre avec la clause DELETE)<br><br>
     * <b>Exemple d'utilisation :</b><code>
     * $MysqlBddDropTable('table_a_supprimer');
     * </code>
     * @param string $table Nom de la table SQL � supprimer
     * @return boolean Retourne TRUE si la suppression c'est bien d�roul� 
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
                // s'il y a eu une erreur on arr�te le script
                throw new Exception("Requ�te DROP TABLE invalide : ".mysql_error());
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
     * Cette m�thode vous permet de cr�er une table SQL.<br><br>
     * <b>Toutes les donn�es dans cette m�thode sont prot�g�es contre l'injection SQL</b><br><br>
     * <b>Exemple d'utilisation</b><code>
     * // 1ere colonne
     * $colonnes_sql1 = array(
     *     "NOM"            => "id", // Nom de la colonne (obligatoire)
     *     "TYPE"           => "INT", // Type de colonne (obligatoire)
     *     "TAILLE"         => "11", // Taille de la colonne (obligatoire)
     *     "VALEUR_DEFAUT"  => "", // Valeur par d�faut
     *     "NULL"           => "1", // NULL (laissez vide le cas contraire)
     *     "AUTO_INCREMENT" => "1", // Colonne auto incr�ment�e (laissez vide le cas contraire)
     *     "INDEX"          => "PRIMARY", // Index de la table (un seul index par table)
     *     "COMMENTAIRES"   => "Com" // Commentaire
     * );
     * // 2e colonne
     * $colonnes_sql2 = array("NOM" => "nom_colonne2", "TYPE" => "VARCHAR", "TAILLE" => "255", "VALEUR_DEFAUT" => "Ma valeur par d�faut");
     * // 3e colonne
     * $colonnes_sql3 = array("NOM" => "nom_colonne3", "TYPE" => "TEXT", "TAILLE" => "");
     * $MysqlBddCreateTable('ma_nouvelle_table', array($colonnes_sql1, $colonnes_sql2, $colonnes_sql3));
     * </code>
     * @param string $nom_table Nom de la table SQL que vous souhaitez cr�er
     * @param array $insertion Elements � ins�rer dans votre table. Les param�tres obligatoires sont : NOM, TYPE et TAILLE
     * @param string $p_engin Moteur de la table. Par d�faut c'est InnoDB
     * @param string $p_charset_defaut Charset par d�faut, la valeur par d�faut est latin1 
     * @return boolean Retourne TRUE si la table a �t� cr��e avec succ�s 
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
                throw new Exception("Impossible de cr�er la table SQL car il manque des �l�ments dans les param�tres de la fonction "."$"."MysqlBddCreateTable.");
            }
            
            // d�but de la creation de la table SQL
            $creation = "CREATE TABLE IF NOT EXISTS `".$this->SqlBdd."`.`".$nom_table."` (";
            $apercu   = "CREATE TABLE IF NOT EXISTS `".$nom_table."` (<br />";
            // on ouvre une boucle for pour r�cup�rer toutes les donn�es en param�tres
            for ($i = 0; $i < count($insertion); $i++) {                
                $col = $insertion[$i];
                
                // on v�rifie que les param�tres NOM, TYPE et TAILLE soivent bien d�clar�s
                if (!isset($col['NOM']) || !isset($col['TYPE']) || !isset($col['TAILLE'])) {
                    throw new Exception("Impossible de cr�er la table SQL car il manque au moins un param�tre \"NOM\", \"TYPE\" ou \"TAILLE\" dans la fonction "."$"."MysqlBddCreateTable.");
                } else if (empty($col['NOM']) || empty($col['TYPE'])) {
                    throw new Exception("Impossible de cr�er la table SQL car au moins un param�tre \"NOM\", ou \"TYPE\" est vide dans la fonction "."$"."MysqlBddCreateTable.");
                }
                
                // on r�cup�re toutes les donn�es et on les prot�ges contre l'injection SQL
                $nom    = MySqlProtectVal($col['NOM']);
                $type   = MySqlProtectVal(strtoupper($col['TYPE']));
                $taille = '('.MySqlProtectVal($col['TAILLE']).')';
                // on r�cup�re aussi les param�tres qui ne sont pas obligatoires
                (isset($col['VALEUR_DEFAUT']) && !empty($col['VALEUR_DEFAUT']))? $defaut = " DEFAULT '".MySqlProtectVal($col['VALEUR_DEFAUT'])."'" : $defaut = '';
                (isset($col['NULL']) && !empty($col['NULL']))? $nule = ' NOT NULL' : $nule = ''; 
                (isset($col['AUTO_INCREMENT']) && !empty($col['AUTO_INCREMENT']))? $autoincrement = ' AUTO_INCREMENT' : $autoincrement = '';
                (isset($col['INDEX']) && !empty($col['INDEX']))? $primarykey = ' '.MySqlProtectVal($col['INDEX']) : $primarykey = '';
                (isset($col['COMMENTAIRES']) && !empty($col['COMMENTAIRES']))? $commentaires = ' COMMENT '." '".MySqlProtectVal($col['COMMENTAIRES'])."'" : $commentaires = '';   
                (isset($col['CHARACTER_SET']) && !empty($col['CHARACTER_SET']))? $character_set = ' '.MySqlProtectVal($col['CHARACTER_SET']) : $character_set = '';
                                
                // si la colonne doit-�tre 'auto increment', il faut supprimer la valeur par d�faut car risque d'erreur et NULL doit �tre NOT NULL
                if (!empty($autoincrement)) $defaut='';
                if (!empty($autoincrement)) $nule = ' NOT NULL';                
                
                // on effectue quelques v�rification dans les param�tres
                if ($type == 'TEXT' || $type=='FLOAT' || $type=='DOUBLE' || $type=='DATE' || $type=='DATETIME' || $type=='TIMESTAMP' || $type=='TIME' || $type=='TINYTEXT' || $type=='MEDIUMTEXT' || $type=='LONGTEXT') {
                    // tout ce qui est de type TEXT, DATE, DATETIME etc... n'ont pas de taille minimum ou maximum, donc on vide $taille
                    $taille = '';
                } 
                else if ($type=='INT' || $type=='TINYINT' || $type=='SMALLINT' || $type=='MEDIUMINT' || $type=='BIGINT') {
                    // si auto increment est actif, il faut vider la valeur par d�faut, sinon il y aurra une erreur
                    if (!empty($autoincrement) && !empty($defaut)) { $defaut = ''; } 
                    // taille par d�faut des param�tres TYPE (num�rique) si le param�tre TAILLE est vide
                    if (empty($taille)) { if ($type=='INT') { $taille='(11)'; } else if ($type=='TINYINT') { $taille='(4)'; } else if ($type=='SMALLINT') { $taille='(6)'; } else if ($type=='MEDIUMINT') { $taille='(9)'; } else if ($type=='BIGINT') { $taille='(20)'; } }
                } 
                else if (empty($taille) && ($type=='VARCHAR' || $type=='CHAR'))  {
                    // si la taille VARCHAR et CHAR est vide
                    $taille = '(255)';
                }                
                else if ($type=='YEAR') {
                    // gestion du type YEAR, la taille doit �tre de 2 ou 4 obligatoirement
                    if ($taille=='2') $taille = '(2)'; else $taille = '(4)';
                }
                else if ($type=='VARCHAR' && $taille > '255') {
                    // si le param�tre TAILLE est sup�rieur � 255, on arr�te le script pour en informer l'utilisateur
                    throw new Exception("Impossible de cr�er la table SQL car la TAILLE du param�tre VARCHAR ne peut pas d�passer 255. Erreur dans la fonction "."$"."MysqlBddCreateTable.");  
                } 
                else if (!empty($primarykey) && $primarykey!='PRIMARY' && $primarykey!='FULLTEXT' && $primarykey!='INDEX' && $primarykey!='UNIQUE') {
                    // si le param�tre INDEX ne contient pas le bon param�tre on arr�te le script pour en informer l'utilisateur
                    throw new Exception("Impossible de cr�er la table SQL car le param�tre INDEX n'est pas valide. Les param�tres valides sont: PRIMARY, UNIQUE, INDEX ou FULLTEXT. Erreur dans la fonction "."$"."MysqlBddCreateTable.");                    
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
            
            // cr�ation de la table dans la base de donn�es
            $requete=mysql_query($creation);
            if (!$requete){
                // s'il y a eu une erreur on arr�te le script
                throw new Exception("Requ�te CREATE TABLE invalide : ".mysql_error());
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
     * L'instruction SELECT est utilis�e pour s�lectionner des donn�es � partir d'une base de donn�es.<br><br>
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
     * @param string $table Nom de la table de votre base de donn�es
     * @param string $colonne Noms des colonnes � s�lectionner ou � afficher
     * @param type $clauses Clauses au format string ou array. Ce param�tre vous permet d'y mettre vos clauses WHERE, ORDER, LIMIT etc...<br><b>(ce param�tre n'est pas prot�g� contre l'injection SQL)</b>
     * @param boolean $avec_while Si vous ne voulez pas que cette m�thode utilise la boucle WHILE mettez false, par d�faut ce param�tre est TRUE
     * @return array 
     */
    public function MysqlSelect($table, $colonne='*', $clauses='', $avec_while=true)
    {
        try {     
            // reconnecte automatiquement si $CloseMysqlAuto est TRUE
            if($this->CloseMysqlAuto && mysql_ping()){
                $this->MysqlOpen ($this->SqlHost, $this->SqlUser, $this->SqlPass, $this->SqlBdd);
            }
            
            // si la variable $colonne est vide, on lui met par d�faut un *
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
     * L'instruction SELECT est utilis�e pour s�lectionner des donn�es � partir d'une base de donn�es.<br><br>
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
     * @param string $ligneSql Ligne compl�te de votre requ�te SQL
     * @param boolean $avec_while Si vous ne voulez pas que cette m�thode utilise la boucle WHILE mettez false, par d�faut ce param�tre est TRUE
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
     * Pour les m�thodes MysqlSelect et MysqlSelectPerso<br>
     * @param string $ligneSql
     * @param boolean $avec_while
     * @return array 
     */
    private function SelectBase($ligneSql, $avec_while)
    {
        try {
            // d�claration des variables
            $tab=array();
            
            // ouverture de la requ�te
            $req = mysql_query($ligneSql);
            if(!$req) {
                // s'il y a eu une erreur on arr�te le script
                throw new Exception("Requ�te SELECT invalide : ".mysql_error());
            }            
            // on r�cup�re le nombre de colonne disponible dans la table SQL
            $NbColonnes = mysql_num_fields($req);

            // si $avec_while est �gal � true, on utilisera une boucle WHILE
            if ($avec_while)
            {
                // D�claration d'une variable qui servira d'index pour le tableau ($tab)
                $NumIndex=0;
                // ouverture d'une boucle pour r�cup�rer toutes les valeurs de la BDD demand�es
                while($temp = mysql_fetch_array($req)) 
                {           
                    // ouverture d'une boucle pour r�cup�rer les noms de toutes les colonnes
                    for ($i=0; $i < $NbColonnes; $i++) 
                    {
                        // on stocke le nom de la colonne dans une variable
                        $NomColonne = mysql_field_name($req, $i);
                        // on pr�pare notre tableau final
                        $tab[$NumIndex][$NomColonne] = $temp[$NomColonne];
                    }
                    // indexation de l'index du tableau ($tab)
                    $NumIndex++;
                }               
            }
            // autrement si $avec_while est �gal � false...
            else 
            {
                $temp = mysql_fetch_array($req);
                // ouverture d'une boucle pour r�cup�rer les noms de toutes les colonnes
                for ($i=0; $i < $NbColonnes; $i++) 
                {
                    // on stocke le nom de la colonne dans une variable
                    $NomColonne = mysql_field_name($req, $i);
                    // on pr�pare notre tableau final
                    $tab[$NomColonne] = $temp[$NomColonne];
                }                
            }           

            // on lib�re notre requ�te SQL
            mysql_free_result($req);
            
            // on retourne le tableau
            return $tab;
            
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        } 
    }    
    
    
    /**
     * <b>Toutes les valeurs mise � jour dans cette m�thode sont prot�g�s contre l'injection SQL. Seule le param�tre $clauses n'est pas prot�g�.</b><br><br>
     * <b>Exemple 1</b><code>
     * $MysqlDelete("matable", "WHERE id=7");
     * </code>
     * <b>Exemple 2</b><code>
     * $MysqlDelete("matable", array(array("WHERE" => "id=7")));
     * </code>
     * @param string $table Nom de la table de votre base de donn�es
     * @param type $clauses Clauses au format string ou array. Ce param�tre vous permet d'y mettre vos clauses WHERE, ORDER, LIMIT etc...<br><b>(ce param�tre n'est pas prot�g� contre l'injection SQL)</b>
     * @return boolean 
     */
    public function MysqlDelete($table, $clauses='')
    {
        try {          
            // reconnecte automatiquement si $CloseMysqlAuto est TRUE
            if($this->CloseMysqlAuto && mysql_ping()){
                $this->MysqlOpen ($this->SqlHost, $this->SqlUser, $this->SqlPass, $this->SqlBdd);
            }
            
            // on pr�pare la requ�te SQL            
            $requete = mysql_query("DELETE FROM `".$table."`".$this->Clauses($clauses));
            // si une erreur est arriv�e, on arr�te le script pour g�n�rer une erreur
            if (!$requete) {
                // s'il y a eu une erreur on arr�te le script
                throw new Exception("Requ�te DELETE invalide : ".mysql_error());
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
     * Permet de mettre � jour des enregistrements existants dans une table<br /><br />
     * <b>Toutes les valeurs mise � jour dans cette m�thode sont prot�g�s contre l'injection SQL. Seule le param�tre $clauses n'est pas prot�g�.</b><br><br>
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
     * @param string $table Nom de la table de votre base de donn�es
     * @param array $mise_a_jour Nom des colonnes et valeurs � mettre � jour
     * @param type $clauses Clauses au format string ou array. Ce param�tre vous permet d'y mettre vos clauses WHERE, ORDER, LIMIT etc...<br><b>(ce param�tre n'est pas prot�g� contre l'injection SQL)</b>
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
            // on r�cup�re les valeurs � mettre � jour dans un tableau
            foreach ($mise_a_jour as $cle => $valeur) {                
                $maj[] = "`".$cle."`"."='".MySqlProtectVal($valeur)."'";                
            }           
            // on pr�pare la requ�te SQL            
            $requete = mysql_query("UPDATE `".$table."` SET ".implode(',',$maj)."  ".$this->Clauses($clauses));
            
            // si une erreur est arriv�e, on arr�te le script pour g�n�rer une erreur
            if (!$requete) {
                // s'il y a eu une erreur on arr�te le script
                throw new Exception("Requ�te UPDATE invalide : ".mysql_error());
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
     * Retourne le nombre de lignes qui correspond � un crit�re sp�cifique
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
     * @param string $table Nom de la table de votre base de donn�es
     * @param string $compter Nom de la colonne � compter, par d�faut c'est * (tout)
     * @param type $clauses Clauses au format string ou array. Ce param�tre vous permet d'y mettre vos clauses WHERE, ORDER, LIMIT etc...<br><b>(ce param�tre n'est pas prot�g� contre l'injection SQL)</b>
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
            // on lance la requ�te
            $requete = mysql_query("SELECT COUNT(".$compter.") FROM `".$table."`".$this->Clauses($clauses));
            if(!$requete) {       
                // s'il y a eu une erreur on arr�te le script
                throw new Exception("Requ�te COUNT invalide : ".mysql_error());
            }
            // on r�cup�re le r�sultat de notre requ�te
            $resultat = mysql_fetch_row($requete);
            // on lib�re la requ�te
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
     * Cette m�thode permet d'ins�rer de nouveaux enregistrements dans un tableau<br><br>
     * <b>Toutes les valeurs dans cette m�thode sont prot�g�s contre l'injection SQL</b><br><br>
     * <b>Exemple d'utilisation :</b><code>
     * $MysqlInsert('matable', array(
     *     "Colonne1" => "Valeur",
     *     "Colonne2" => "Valeur",
     *     "Colonne3" => "Valeur"
     * ));
     * </code>
     * @param string $table Nom de la table de la base de donn�es
     * @param array $insertion Elements � ins�rer dans la base de donn�es
     * @return boolean 
     */
    public function MysqlInsert($table, $insertion)
    {   
        try {
            // reconnecte automatiquement si $CloseMysqlAuto est TRUE
            if($this->CloseMysqlAuto && mysql_ping()){
                $this->MysqlOpen ($this->SqlHost, $this->SqlUser, $this->SqlPass, $this->SqlBdd);
            }
            
            // on r�cup�re les valeurs, on utilisera array_map() pour inclure la fonction
            // MySqlProtectVal et ainsi prot�ger les donn�es contre l'injection SQL
            $valeurs = array_map('MySqlProtectVal', array_values($insertion));
            // on r�cup�re les clefs du tableau
            $clefs = array_keys($insertion);
            // insertion des �l�ments dans la base de donn�es
            $requete=mysql_query("INSERT INTO `".$table."` (`".implode("`,`",$clefs)."`) VALUES ('".implode("','",$valeurs)."')");
            if(!$requete) {                
                throw new Exception("Requ�te INSERT invalide : ".mysql_error());
            }
            // on r�cup�re l'identifiant de la requete ajout�
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
     * Gestion des clauses pour la base de donn�es
     * @param type $clauses Clauses au format string ou array. Ce param�tre vous permet d'y mettre vos clauses WHERE, ORDER, LIMIT etc...<br><b>(ce param�tre n'est pas prot�g� contre l'injection SQL)</b>
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
                        // on force la clef a �tre en majuscule
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
     * <p>Connexion � la base de donn�es</p>
     * @param string $BddHost Nom du serveur MySql (en local c'est localhost)
     * @param string $BddUser Nom utilisateur (en local mettez root)
     * @param string $BddPass Mot de passe (en local, laissez vide)
     * @param string $BddName Nom de la base de donn�es
     * @return boolean Retourne TRUE si la connexion c'est d�roul�e avec succ�s
     */
    public function MysqlOpen($BddHost='', $BddUser='', $BddPass='', $BddName='')
    {
        $resultat = true;       
        try {            
            // si l'utilisateur donne les informations de connexion dans les param�tres de cette m�thode
            if(!empty($BddHost) && !empty($BddName) && !empty($BddUser))
            {
                // on stocke les donn�es de connexion dans les variables
                $this->SqlHost = $BddHost;
                $this->SqlUser = $BddUser;
                $this->SqlPass = $BddPass;
                $this->SqlBdd  = $BddName;
            }
            
            // si l'utilisateur n'a donn� aucune information de connexion
            if(empty($this->SqlBdd) && empty($this->SqlHost) && empty($this->SqlUser))
            {
                // on arr�te le script pour afficher un message d'erreur
                throw new Exception("Vous devez indiquer vos informations de connexion");
            }
            
            // Ouverture de la connexion au serveur MySQL 
            if (!@mysql_connect($this->SqlHost, $this->SqlUser, $this->SqlPass)) {
                $resultat = false;
                // en cas d'erreur on arr�te le script
                throw new Exception("Erreur de connexion au serveur ".$this->SqlHost);                      
            }            
            // S�lection de la base de donn�es MySql
            if ($resultat && !@mysql_select_db($this->SqlBdd)) {
                // en cas d'erreur on arr�te le script
                throw new Exception("Erreur de connexion � la base de donn�es"); 
            }            
        } catch (Exception $exc) {
            $this->GestionException($exc->getMessage());
        }        
        return $resultat;
    }    
    
    /**
     * Met fin � la connection
     * @return boolean
     */
    public function MysqlClose()
    {
        mysql_close();
        return true;
    }
    
    /**
     * G�re les messages d'erreur sur un try catch
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
 * Cette m�thode prot�ge la base de donn�es contre les injections SQL<br>
 * @param string $chaine Chaine � prot�ger
 * @return string 
 */
function MySqlProtectVal($chaine)
{
    // SI VOUS VOULEZ MODIFIER CETTE FONCTION, VOUS DEVEZ PRENDRE CERTAINES PRECAUTIONS:
    // 1 - NE PAS DEPLACER CETTE FONCTION, ELLE DOIT RESTER OU ELLE EST
    // 2 - NE PAS MODIFIER LE NOM DE CETTE FONCTION

    // on supprime les caract�res invisibles en d�but et fin de cha�ne
    $chaine = trim($chaine);

    // si magic quote est activ� on ajoute des slashes
    if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) 
        $chaine = stripslashes($chaine);

    // si la fonction mysql_real_escape_string existe on l'utilise plut�t que addslashes qui est moins performant
    if (function_exists('mysql_real_escape_string')) 
        $chaine = mysql_real_escape_string($chaine);
    else 
        $chaine = addslashes($chaine);    

    return $chaine; // on retourne le r�sultat final     
}
?>