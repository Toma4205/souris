<?php
class ConnexionBDD
{
   /**
   * Instance de la classe SPDO
   *
   * @var SPDO
   * @access private
   * @static
   */
  private static $instance = null;

  /**
   * Constructeur
   *
   * @param void
   * @return void
   * @access private
   */
  private function __construct()
  {
  }

   /**
    * Crée et retourne l'objet SPDO
    *
    * @access public
    * @static
    * @param void
    * @return SPDO $instance
    */
  public static function getInstance()
  {
    if(is_null(self::$instance))
    {
      try
      {
        self::$instance = new PDO('mysql:host=localhost;dbname=id3559928_souris;charset=utf8', 'souris', 'souris',
          array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
      }
      catch(Exception $e)
      {
        die('Erreur : '.$e->getMessage());
      }
    }
    return self::$instance;
  }

  public function __clone()
  {
    throw new Exception("Impossible de cloner la connexion.");
  }
}
?>
