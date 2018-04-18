<?php
// entete
require_once("vue/commun/enteteflex.php");
?>
	  
	  <style type="text/css">
		.col-md-8, .col-sm-10 { line-height: 40px; text-align: center;}
		.col-md-12 { line-height: 80px; text-align: center;}
		
		.nav>li>a {
			padding-top: 4px;
			padding-bottom: 4px;
		}
	  </style>
		

	  <div class="container-fluid">
      		<div class="row">
				<div class="col-sm-3">
					<ul class="nav nav-pills nav-stacked">
						<li role="presentation" class="active"><a href="#speed">En speed</a></li>
						<li role="presentation"><a href="#debuter">Pour débuter</a></li>
						<li role="presentation"><a href="#nouvelle">Nouvelle ligue</a></li>
						<li role="presentation"><a href="#mercato">Mercato</a></li>
						<li role="presentation"><a href="#prix">Prix et Budget</a></li>
						<li role="presentation"><a href="#calcul">Calcul des notes</a></li>
						<li role="presentation"><a href="#compo">Compo  et Capitaine</a></li>
						<li role="presentation"><a href="#remplacements">Remplacements</a></li>
						<li role="presentation"><a href="#bonus">Bonus - Malus</a></li>
						<li role="presentation"><a href="#evenements">Evenements</a></li>
						<li role="presentation"><a href="#buts">Marquer des buts</a></li>
						<li role="presentation"><a href="#tonton">Tonton Pat et P'tit jeune</a></li>
						<li role="presentation"><a href="#gagner">Gagner une ligue</a></li>
						<li role="presentation"><a href="#conference">Conférence de presse</a></li>
					  </ul>
				</div>	
				<div class="col-sm-9">
					<div class="row">
					<div id="speed" class="col-sm-12">
						<h3><span class="label label-default pull-left">En Speed</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							Classicoach est un jeu de fantasy football complètement dingue consacré au championnat français.
							<br/>
							Dans une ligue de 2 à 12 participants, vous allez vous répartir les joueurs de Ligue 1 lors d'un mercato ou d'une draft afin de composer votre équipe fantasy pour la saison. Chaque journée de championnat, tu affronteras l'équipe d'un de tes adversaires. Chaque but marqué par tes joueurs en réalité comptera aussi dans ta ligue. Les bonnes notes de tes joueurs obtenues grâce à leurs performances réelles te permetteront aussi de marquer des buts sur Classicoach.
							<br/>
							Une victoire te rapporte 3 points et un match nul 1 point. Lorsque tous les matchs aller-retour seront joués, le gagnant sera celui qui aura obtenu le plus de points. Il deviendra alors le maître incontesté et pourra se permettre de chambrer ouvertement ses adversaires. Le dernier, la souris, aura le droit de se taire à tout jamais.
						</div>
					</div>
					<div id="debuter" class="col-sm-12">
						<h3><span class="label label-default pull-left">Pour débuter</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							Première étape, l'inscription #LaBase : un pseudo, un mot de passe et c'est parti ! Tout se gère ensuite dans la page "Mon Compte".
							<br/>
							<strong>Trouve tes confrères </strong> à partir de leur pseudo pour pouvoir les inviter dans tes ligues et pour suivre leur actualité. L'élite du football est sur Classicoach, à toi de forger ta réputation. Alors plutôt Didier ou plutôt José ?
						</div>
					</div>
					<div id="nouvelle" class="col-sm-12">
						<h3><span class="label label-default pull-left">Nouvelle ligue</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							Si tu es un <strong>suiveur</strong> (et il n'y a pas de honte à ça), tu te feras surement inviter à rejoindre la ligue d'un de tes confrères. Cette invitation apparaîtra sur la page "Mon Bureau". Tu seras notifié de la validation de ta candidature sur cette même page. 
							<br/>
							En tant que <strong>leader</strong> tu peux <strong>créer une ligue</strong>. Commence par lui choisir un nom et personnalise la avec toutes les options qui s'offrent à toi :
							<br/>
							<ul>
							  <li>Pack Bonus/Malus : </li>
								<ul>
								  <li>Aucun : A l'ancienne, pas de bonus/malus, seul le talent sur le terrain compte</li>
								  <li>Classique : Un assortiment de différents bonus/malus prédéfinis en fonction du nombre de joueur dans la ligue</li>
								  <li>Personnalisé : Choisis quelques bonus/malus parmis la liste de ceux disponibles</li>
								</ul>
							  <li>Mode Expert : Dans ce mode, le poste de chaque joueur compte (un défenseur central n'est pas un défenseur latéral par exemple, ET OUAIS ON RIGOLE PLUS !) et tu ne peux aligner que 4 joueurs d'un même club sur ta feuille de match</li>
							  <li>Mode Mercato : Comment souhaitez-vous vous répartir les joueurs "Draft" ou "Mercato" ? (expliqué plus bas)</li>
							  <li>Un petit pari ? Tu peux mettre un peu de piment et d'enjeu dans ta ligue</li>
							</ul>
						</div>
					</div>
					<div id="mercato" class="col-sm-12">
						<h3><span class="label label-default pull-left">Mercato</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							<strong>Le mode MERCATO : </strong> Tu disposes d'un budget de 400 M€ (virtuels évidemment !) pour enchérir sur des footballeurs et constituer ton équipe. Dès que tout le monde a validé ses enchères, si tu as été le plus offrant sur un footballeur (ou le plus rapide en cas d'égalité), alors il rejoint ton équipe. Tu récupères le budget de tes enchères perdues pour un nouveau tour. Et on continue jusqu'à ce que tu valides ton équipe pour la saison. 
							<br/>
							<strong>Le mode DRAFT : </strong> Chacun votre tour (dans un ordre défini au hasard), vous allez pouvoir recruter le footballeur de votre choix. Au tour suivant, l'odre s'inverse, et le dernier recrute en premier, et ainsi de suite jusqu'à ce que tu valides ton équipe pour la saison.
							<br/>
							<strong>Effectif minimum d'une équipe : </strong>
							<ul>
							  <li>2 Gardiens</li>
							  <li>6 Défenseurs</li>
							  <li>6 Milieux</li>
							  <li>3 Attaquants</li>
							</ul>
						</div>
					</div>
					<div id="prix" class="col-sm-12">
						<h3><span class="label label-default pull-left">Prix et Budget</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							<strong>Le prix des footballeurs</strong> est estimé grâce à un algorithme savant qui s'appuie uniquement sur la véritable valeur marchande du joueur. Les statistiques et les performances ne sont pas prises en compte dans le calcul. Il y a donc des <strong>pépites</strong> à dénicher et des mauvaises affaires à éviter ! Nous ajustons régulièrement le prix des joueurs en suivant l'actualité.
							<br/>
							<strong>Le budget</strong> virtuel de 400M€ est suceptible d'évoluer d'une saison à l'autre en fonction des plus-values réalisées sur vos différentes pépites. En Mode Super Dénicheur De Talents !
						</div>
					</div>
					<div id="calcul" class="col-sm-12">
						<h3><span class="label label-default pull-left">Calcul des notes</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							<strong>La coeur du jeu : LES NOTES ! </strong>Attention voici le gros morceau du règlement. Nous allons faire preuve de pédagogie.
							<br/>
							<strong>La note de base</strong> de chaque joueur est calculée par Classicoach en fonction de son poste, de sa prestation collective, et individuelle. Plusieurs dizaines de statistiques sont prises en compte dans cette note (passes réussies, tacles ratés, bonne sortie du gardien, temps de jeu, etc.). Les notes varient de 0 à 10 et pour un match moyen (ni bon, ni mauvais) un joueur obtient la note de 5/10.
							<br/>
							<strong>Les bonus tactiques : </strong> Si tu positionnes 4 défenseurs, leur note sera réhaussée de +0,5 (les titulaires uniquement). Avec 5 défenseurs, le bonus est de +1 !
							<br/>
							<strong>L'impact des BONUS/MALUS : </strong> Certains BONUS/MALUS affectent la note des joueurs de manière positive ou négative (voir BONUS/MALUS)
							<br/>
							<strong>Les matchs annulés ou reportés</strong> confèrent une note de 5/10 à tous les footballeurs des équipes concernées.
							<br/>
							<strong>Vous avez le droit de critiquer le système de notation</strong> ... mais ça ne changera pas grand chose.
						</div>
					</div>
					<div id="compo" class="col-sm-12">
						<h3><span class="label label-default pull-left">Compo et Capitaine</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							<strong>La saison est lancée ! </strong>Pensez à enregistrer votre composition d'équipe avant le début du premier match de chaque journée de championnat réel. Il ne sera plus possible de modifier votre composition durant le weekend.
							<br/>
							<strong>Pense-bête avant chaque journée de championnat : </strong>
							<ul>
							  <li>Choisis ta tactique</li>
							  <li>Choisis un titulaire pour chaque poste</li>
							  <li>Choisis un capitaine (voir ci-dessous)</li>
							  <li>Vérifie l'actualité de tes joueurs (blessés, suspendus, incertains)</li>
							  <li>Choisis un éventuel Bonus/Malus à appliquer sur ce match</li>
							  <li>Choisis une liste de remplaçants et définis tes règles de remaplacement (voir ci-dessous)</li>
							  <li>Comme des frangins qui jouent au hand, tu peux parier sur le résultat exact de ton propre match. Tu gagneras un nouveau Bonus/Malus en cas de succès. Tu n'as rien à perdre donc JOUES !</li>
							</ul>
							<br/>
							<strong>Le Capitaine</strong> obtiendra une note bonus de +0,5 si son équipe réelle gagne. Il prendra un malus de -1 si son équipe réelle subit une défaite.
						</div>
					</div>
					<div id="remplacements" class="col-sm-12">
						<h3><span class="label label-default pull-left">Remplacements</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							Tu peux aligner 7 remplaçants sur la feuille de match.
							<br/>
							<strong>Stratégie de remplacement : </strong>Comme tu es un fin stratège, tu peux définir des règles de remplacement en fonction de la note obtenue par le titulaire. C'est compliqué ? Attends, prenons un exemple, tu positionnes Marcel en défenseur titulaire et Laurent en défenseur remplaçant. Tu peux définir la règle suivante : Manu remplace Marcel si Marcel a une note inférieur à 5. Capiche !? Il ne peut pas y avoir de stratégie de remplacement sur un gardien (sauf BONUS).
							<br/>
							<strong>Les chouchous du coach : </strong>si tu avais aligné un joueur titulaire qui n'a finalement pas joué en réalité (la honte...), il se fera remplacé par un des remplaçants. L'heureux remplaçant sera le joueur qui a le même poste et qui est assis le plus proche du coach (chouchou). Si aucun remplaçant n'a le même poste que le titulaire absent alors il pourra être remplacé par un poste plus offensif  (un défenseur peut être remplacé par un milieu ou un attaquant et un milieu peut être remplacé par un attaquant). Dans ce cas, le remplaçant ne joue pas à son poste de prédilection, sa note prend donc un malus de -0,5 par poste de différence avec le titulaire. Les gardiens ne peuvent pas être remplacés par un joueur de champ.
						</div>
					</div>
					<div id="bonus" class="col-sm-12">
						<h3><span class="label label-default pull-left">Bonus - Malus</span></h3>
						<br/>
						<br/>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/bonusmalus/PNG_bus.png" alt="Img BUS" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>Le Bus</strong> : c'est la stratégie défensive maximum ! Tu n'encaisseras aucun but basé sur la note des joueurs adverses. Seuls les buts réels de ton adversaire comptera.
									<br/>
									<br/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/bonusmalus/PNG_butcher.png" alt="Img BOUCHER" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>Le Boucher</strong> : tu désignes un de tes titulaires pour aller casser en deux un joueur adverse !!! Résultat : le joueur adverse est complètement anihilé, ses buts ne compteront pas, il est considéré comme absent (donc remplaçable). En contrepartie, ton titulaire BOUCHER prend un rouge et n'est pas remplacé, il obtient la note de 0/10.
									<br/>
									<br/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/bonusmalus/PNG_changementGardien.png" alt="Img Changement Gardien" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>Changement de Gardien</strong> : tu as la possibilité de définir une stratégie de remplacement sur ton gardien. Au top pour éviter les matchs-passoires !
									<br/>
									<br/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/bonusmalus/PNG_diner.png" alt="Img Diner" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>Un petit Diner avec l'arbitre</strong> offre de grandes possibilités (la cantine du Barça est d'ailleurs bien occupée) ... Après quelques discussions avec l'arbitre, vous vous mettez d'accord pour retirer un but à votre adversaire. Un coup de sifflet qui peut tout changer !
									<br/>
									<br/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/bonusmalus/PNG_family.png" alt="Img Family" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>La Famille est au stade !</strong> Papy, Mamy, Tonton, Tata, Papa, Maman, le chien, et compagnie ont préparé des énormes pancartes pour encourager le petit dernier de la famille : +1 sur la note du joueur en question.
									<br/>
									<br/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/bonusmalus/PNG_fumigenes.png" alt="Img Fumigènes" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>Fumigènes</strong> : les stadiers étaient à la buvette, les supporters ont pu faire entrer des fumiènes dans le stade. Derrière le but du gardien adverse, une épaisse fumée blanche monte depuis les tribunes. La visibilité du gardien est diminuée : -1 sur sa note finale.
									<br/>
									<br/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/bonusmalus/PNG_mauvaisCrampon.png" alt="Img Mauvais Crampon" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>Mauvais Crampon</strong> : le joueur adverse de ton choix a chaussé les mauvais crampons. Il n'était déjà pas très bon avec de bonnes chaussures, le voilà encore plus mauvais : -1 sur sa note finale.
									<br/>
									<br/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/bonusmalus/PNG_zizou.png" alt="Img Zizou" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>Conseils de Zizou</strong> : le grand, l'unique, le magnifique Zizou vient rencontrer tes joueurs dans les vestiaires avant le match. Ses conseils sont précieux et l'équipe en sort grandie : +0,5 pour tous tes titulaires.
									<br/>
									<br/>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/bonusmalus/PNG_surprise.png" alt="Img Surprise" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>Encore plein d'autres Bonus/Malus à venir ...</strong>
									<br/>
									<br/>
								</div>
							</div>
						</div>
					</div>
					<div id="evenements" class="col-sm-12">
						<h3><span class="label label-default pull-left">Evenements Exceptionnels</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							Plusieurs évenements exceptionnels peuvent survenir durant une journée de championnat sur Classicoach. De manière aléatoire, ils apparaissent et peuvent changer le cours du match.
							<br/>
						</div>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/bonusmalus/PNG_selection.png" alt="Img Sélectionneur" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>Un Sélectionneur National dans les tribunes</strong> observe tous les joueurs du pays qu'il représente. C'est l'occasion rêvée pour les footballeurs partageant la même nationalité que le sélectionneur : +0,5 pour eux.
									<br/>
									<br/>
								</div>
							</div>						
						</div>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/bonusmalus/PNG_surprise.png" alt="Img Surprise" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>Encore plein d'autres Evenements Exceptionnels à venir ...</strong>
									<br/>
									<br/>
								</div>
							</div>
						</div>
					<div id="buts" class="col-sm-12">
						<h3><span class="label label-default pull-left">Marquer des buts</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							Il existe 2 façon de marquer des buts contre ton adversaire sur Classicoach
							<br/>
							<strong>Buts réels</strong> : Le plus simple, le plus logique, tous les buts marqués par des joueurs présents dans la compo finale compteront. #LaBase.
							<br/>
							<strong>Buts talentueux</strong> (valable uniquement pour les joueurs n'ayant pas marqué de but en réalité) : un joueur peut marquer un but de talent si sa note est suffisament élevée pour passer les lignes adverses.
							<br/>
							Pour marquer,
							<ul>
							  <li>Un défenseur doit passer les lignes d'Attaque, de Milieu, de Défense et le Gardien</li>
							  <li>Un milieu doit passer les lignes de Milieu, de Défense et le Gardien</li>
							  <li>Un attaquant doit passer la ligne d'Attaque et le Gardien</li>
							</ul>
							<br/>
							Pour <strong>passer une ligne</strong>, on compare la note du joueur à la moyenne des notes de la ligne. Si la note est supérieure à la moyenne alors le joueur passer la ligne.
							<br/>
							<strong>Epuisement</strong> : pour transpercer les lignes adverses, ton joueur utilise beaucoup d'énergie. Pour le calcul des buts de talent, sa note baisse au fur et à mesure qu'il affronte de nouvelles lignes : -1 après avoir passé la première ligne, et -0,5 après avoir passé les lignes suivantes.
							<br/>
							<strong>Avantage Domicile</strong> : si la note du joueur est égal à la moyenne de la ligne, alors le footballeur passe SEULEMENT si il joue à Domicile (la puissance du public sans doute).
							<br/>
							<strong>Le petit exemple qui va bien</strong> : un de mes milieux a obtenu la note de 7. Pour marquer un but talentueux il doit passer les lignes adverses de Milieu, de Défense et le Gardien. La moyenne des notes des Milieux adverse est de 6 (7 est supérieur à cette moyenne) donc mon joueur passe cette ligne ! Fatigué par cet effort, sa note prend -1 et devient donc 6/10 pour affronter la ligne de Défense adverse qui a une moyenne de 5,7 (6 est supérieur à cette moyenne). Quelle percée, notre joueur passe aussi cette ligne et perd de l'énergie. Sa note prend -0,5 et devient donc 5,5/10. Face à face avec le gardien adverse qui a une note de 5, notre milieu  marque un splendide but talentueux (5,5 est supérieur à la note du gardien). GOOAALL !						
						</div>
					</div>
					<div id="tonton" class="col-sm-12">
						<h3><span class="label label-default pull-left">Tonton Pat et P'tit jeune</span></h3>
						<br/>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/tontonpat.png" alt="Img Tonton Pat" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									<strong>Tonton Pat</strong>, il est possible que tu n'arrives pas à aligner 11 joueurs dans ta composition (absences, blessures, suspensions ou tout simplement bétise). Dans ce cas, tu joues en infériorité numérique pendant tout le match, un Tonton Pat se produit (clin d'oeil à notre ami Patoche). Un Tonton Pat baisse la moyenne de sa ligne de -1,5 laissant la place aux buts talentueux de ton adversaire.
									<br/>
									<br/>
								</div>
							</div>						
						</div>
						<div class="row">
							<div class="col-sm-3">
								<img src="web/img/jeuneclub.png" alt="Img Jeune Club" height="70" width="70">
							</div>
							<div class="col-sm-9">
								<div style="line-height : 20px; text-align: justify;">
									Il est possible que tu te retrouves sans gardien, dans ce cas, les dirigeants demandent à <strong>Un Jeune du Club</strong>, habitué à porter les bouteilles d'eau,  d'enfiler les gants et d'aller se mettre dans les cages. Il aura la note de 1/10 
									<br/>
									<br/>
								</div>
							</div>						
						</div>
					</div>
					<div id="gagner" class="col-sm-12">
						<h3><span class="label label-default pull-left">Gagner une ligue</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							<strong>Winner ! </strong>Le joueur qui aura cumulé le plus de points en fin de saison sera sacré Champion. En cas d'égalité, le coach avec la meilleure différence de but gagnera. Si l'égalité persiste, le coach qui aura subi le plus de BONUS/MALUS de la part de ses adversaires sera désigné vainqueur.
						</div>
					</div>
					<div id="conference" class="col-sm-12">
						<h3><span class="label label-default pull-left">Conférence de presse</span></h3>
						<br/>
						<div style="line-height : 20px; text-align: justify;">
							<br/>
							<strong>Un petit mot coach, s'il vous plait ? </strong>La traditionnelle conférence de presse est présente sur Classicoach. Avant ou après un match, vous pouvez répondre aux questions parfois très orientées des journalistes pour saluer ou chambrer votre adversaire.
						</div>
					</div>
					</div>
				</div>
			</div>
      </div>
	  </div>
	  

<?php
// Le pied de page
require_once("vue/commun/pied_de_pageflex.php");
?>