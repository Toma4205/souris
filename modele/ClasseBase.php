<?php

class ClasseBase {

	/**
	 * @access public
	 * @param array $donnees
	 * @return void
	 */
	public final  function hydrate($donnees)
	{
		foreach ($donnees as $key => $value)
		{
			// On récupère le nom du setter correspondant à l'attribut.
			$method = 'set'.ucfirst($key);

			// Si le setter correspondant existe.
			if (method_exists($this, $method))
			{
				// On appelle le setter.
				$this->$method($value);

				//echo '<b> Méthode -> ' . $method . '</b><br/>';
			}
			else {
				//echo '<b> Méthode inconnue -> ' . $method . '</b><br/>';
			}
		}
	}

}
?>
