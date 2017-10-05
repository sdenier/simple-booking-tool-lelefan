<?php

$csv_file = "booked.csv";

$to_email = "creneaux@lelefan.org";

$max_3h = "Laisses donc des creneaux pour les autres, c'est 3h max par mois :)";
$max_5p = "Oups, 5 c'est bien assez, essaie un autre creneau !";

function readCSV($csvFile){
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle) ) {
        $line_of_text[] = fgetcsv($file_handle, 1024);
    }
    fclose($file_handle);
    return $line_of_text;
}
function writeCSV($csvFile,$list){
    $fp = fopen($csvFile, 'w');
	foreach ($list as $fields) {
	    fputcsv($fp, $fields);
	}
	fclose($fp);
}

$cookie_val = floatval($_COOKIE["booked"]);

$messages = array();
$messages["succes"] = array();
$messages["error"] = array();
$messages["warning"] = array();

$creneaux = array();
$creneaux[0] = '6h-7h30';
$creneaux[1] = '7h30-10h30';
$creneaux[2] = '10h30-13h30';
$creneaux[3] = '13h30-16h30';
$creneaux[4] = '16h30-19h30';
$creneaux[5] = '19h30-21h';

$jours = array();
$jours[0] = 'vendredi 6 octobre';
$jours[1] = 'Samedi 7 octobre';
$jours[2] = 'vendredi 13 octobre';
$jours[3] = 'Samedi 14 octobre';
$jours[4] = 'vendredi 20 octobre';
$jours[5] = 'Samedi 21 octobre';
$jours[6] = 'vendredi 27 octobre';
$jours[7] = 'Samedi 28 octobre';

if ($_POST && isset($_POST["ok"]) && $_POST["ok"]){
	if (isset($_POST["lastname"])&&$_POST["lastname"]&&isset($_POST["firstname"])&&$_POST["firstname"]){
		if (isset($_POST["email"])&&$_POST["email"]){
			if (isset($_POST["raison"])){
			    $subject = '[ONLINE FORM] Impossible de reserver un creneau';
			    $message = $_POST["lastname"].' '.$_POST["firstname"].' '.$_POST["email"].' '.$_POST["raison"].' '.$_POST["message"];
			    $headers = 'From: creneaux@lelefan.org' . "\r\n" .
			     'Reply-To: '. $_POST["email"] . "\r\n" .
			     'X-Mailer: PHP/' . phpversion();
			    mail($to_email, $subject, $message, $headers);
				header('Location: '.$_SERVER['PHP_SELF']."?success=true");
				exit;
			}else{
				if (intval($_COOKIE["booked"])<3){
					if (isset($_POST['creneau'])&&isset($_POST['jour'])){
						$booked = readCSV($csv_file);
						if ($booked[$_POST['creneau']][$_POST['jour']]<5){
							$booked[$_POST['creneau']][$_POST['jour']] += 1;
							writeCSV($csv_file,$booked);
						    $subject = '[ONLINE FORM] nouveau creneau reservé';
						    $message = $_POST["lastname"].' '.$_POST["firstname"].' '.$_POST["email"].' '.$_POST['ok'];
						    $headers = 'From: creneaux@lelefan.org' . "\r\n" .
						     'Reply-To: '. $_POST["email"] . "\r\n" .
						     'X-Mailer: PHP/' . phpversion();
						    mail($to_email, $subject, $message, $headers);
						    if ($_POST['creneau']==0 || $_POST['creneau']==5)
						     	$cookie_val += 1.5;
						    else
								$cookie_val += 3;
						    setcookie("booked",$cookie_val);
						    //success, redirect same file
							header('Location: '.$_SERVER['PHP_SELF']."?success=true");
							exit;
						}else{
							$messages['error'][] = $max_5p;
						}
					 }else{
					 	$messages['error'][] = "formulaire incomplet";
					 }
				}else{
					$messages['warning'][] = $max_3h;
				}
			}
		}else{
			$messages['error'][] = "Ton email est necessaire pour pouvoir te recontacter ;)";
		}
	}else{
		$messages['error'][] = "Merci de bien spécifier ton nom et prénom :)";
	}
}
if (isset($_GET['success'])&&$_GET['success']){
	$messages['success'][] = "Merci, ta demande a bien été prise en compte. <br/>A bientôt dans ton épicerie";
}

$colors = array();
$colors[0] = 'red';
$colors[1] = 'amber';
$colors[2] = 'yellow';
$colors[3] = 'lime lighten-2';
$colors[4] = 'light-green lighten-3';
$colors[5] = 'green lighten-4';


$booked = readCSV($csv_file);

?><!DOCTYPE html>
  <html>
    <head>
		<!--Import Google Icon Font-->
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<!--Import materialize.css-->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
		<title>L'éléfàn Creneaux Oct</title>
		<!--Let browser know website is optimized for mobile-->
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    </head>
    <body>
    	<?php foreach ($messages["success"] as $value) { ?>
    		<div class="card-panel green lighten-2"><?php echo $value ?></div>
    	<?php } ?>
    	<?php foreach ($messages["error"] as $value) { ?>
    		<div class="card-panel red lighten-2"><?php echo $value ?></div>
    	<?php } ?>
    	<?php foreach ($messages["warning"] as $value) { ?>
    		<div class="card-panel orange lighten-2"><?php echo $value ?></div>
    	<?php } ?>
    	<div class="container">
    		<div class="section">
      			<div class="row">
      				<h2>
				    	Gestion du bénévolat mois d'octobre
				    </h2>
				    <p>
				    	Clique sur une case pour t'inscrire sur le creneau associé.
				    </p>
					<table class="responsive-table" id="tab">
						<thead>
							<tr>
								<td>
								</td>
								<?php foreach ($jours as $indexj => $jour) { ?>
									<td><?php echo $jour; ?></td>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($creneaux as $index => $creneau) { ?>
							<tr>
								<td class="right-align"><b><?php echo $creneau; ?></b></td>
								<?php foreach ($jours as $indexj => $jour) { 
									$nb = $booked[$index][$indexj];
									?>
									<td class="creneau <?php echo $colors[$nb]; ?>" 
										data-nb="<?php echo $nb; ?>"
										data-lig="<?php echo $index; ?>"
										data-col="<?php echo $indexj; ?>"
										data-fr="je m'inscrit pour le <?php echo $jour; ?> de <?php echo $creneau; ?>"><?php echo $nb; ?> bénévole<?php echo ($nb)? 's' :'';?> inscrit<?php echo ($nb)? 's' :'';?></td>
								<?php } ?>
							</tr>
						<?php } ?>
						</tbody>
					</table>
					<br>
					<br>
					<a class="btn waves-effect waves-light red modal-trigger" href="#impossible">Oups, je ne peux pas m'inscrire :(</a>
				</div>
			</div>
		</div>
		


<div id="inscription" class="modal">
    <form  action="" method="POST">
    	<div class="modal-content">
    		<input type="text" name="lastname" placeholder="Nom" />
			<input type="text" name="firstname" placeholder="Prenom" />
			<input type="email" name="email" placeholder="mon@email.fr" />
			<input type="hidden" name="creneau" />
			<input type="hidden" name="jour" />
    	</div>
    	<div class="modal-footer">
			<input type="submit" name="ok" value="M'inscrire" class="btn" />
		</div>
	</form>
</div>
<div id="impossible" class="modal">
    <form  action="" method="POST">
    	<div class="modal-content">
    		<p>
		      <input name="raison" type="radio" id="raison1" value="pas disponible sur les créneaux" />
		      <label for="raison1">Je ne suis pas disponible sur les créneaux qui restent à pourvoir</label>
		    </p>
		    <p>
		      <input name="raison" type="radio" id="raison2" value="aucun créneau disponible" />
		      <label for="raison2">Il ne reste aucun créneau disponible</label>
		    </p>
		    <p>
		      <input name="raison" type="radio" id="raison3" checked="checked" value="problème technique" />
		      <label for="raison3">J'ai un problème technique</label>
		    </p>
    		<input type="text" name="lastname" placeholder="Nom" />
			<input type="text" name="firstname" placeholder="Prenom" />
			<input type="email" name="email" placeholder="mon@email.fr" />
			<div class="input-field col s12">
	          <textarea id="message" class="materialize-textarea" name="message"></textarea>
	          <label for="message">Un message facultatif</label>
	        </div>
    	</div>
    	<div class="modal-footer">
			<input type="submit" name="ok" value="Prévenir" class="btn" />
		</div>
	</form>
</div>

      	<!--Import jQuery before materialize.js-->
      	<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
  		<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
  		<?php if ($cookie_val < 3 ){?>
    	<script type="text/javascript">
    		jQuery(function(){
    			$('.modal').modal();
    			$("#tab").on("click","tbody td.creneau",function(){
    				if (parseInt($(this).attr("data-nb"))<5){
    					$("#inscription").find("input[name=ok]").val($(this).attr("data-fr"));
	    				$("#inscription").find("input[name=creneau]").val($(this).attr("data-lig"));
	    				$("#inscription").find("input[name=jour]").val($(this).attr("data-col"));
	    				$("#inscription").modal('open');
    				}else{
    					Materialize.toast('<?php echo addslashes($max_5p); ?>', 3000, 'rounded');
    				}
    				
    			});
    		});
    	</script>
    	<?php }else{ ?>
    	<script type="text/javascript">
    		jQuery(function(){
    			$('.modal').modal();
    			$("#tab").on("click","tbody td.creneau",function(){
    				 Materialize.toast('<?php echo addslashes($max_3h); ?>', 3000, 'rounded');
    			});
    		});
    	</script>
    	<?php } ?>
    </body>
  </html>