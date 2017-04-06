<?php
    session_start();
    if(empty($_SESSION['id']))
    {
        echo '<p style="text-align: center;">Vous devez vous connecter pour acceder à cette page.<p>';
        include('cfcconnexion.php');
        exit();
    }
    //fishing_session_start
    $fishing_session_already_started = FALSE;
    $members = $_SESSION['id'];

    if(isset($_POST['fishing_session_start']))
    {
        if($_POST['place'] == NULL)
        {
            $place_error = '<p>Le lieu doit être renseigné</p>';
        }
        else
        {
          $fishing_place = htmlspecialchars($_POST['place']);
          //$members = $_SESSION['id'];
          //Manip le 03/04 à 21H30 après derniere sauvegarde qui fonctionne
          if(!empty($_SESSION['started']))
          {
            $fishing_session_already_started_error = '<p>Une session est déjà en cours</p>';
          }
          else
          {

          // Database Connexion
          try
          {
            $bdd = new PDO('mysql:host=localhost;dbname=journal;charset=utf8', 'root', 'root', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
          }
          catch (exception $e)
          {
            die ('Erreur:' . $e->getMessage());
          }
          //ISERT start_date, place, id_members INTO fishing_session table
          $req = $bdd->prepare('INSERT INTO fishing_session(start_date, place, id_members) VALUES (now(), :place, :id_members)');
          $req->execute(array(
            'place' => $fishing_place,
            'id_members' => $members));


          $_SESSION['started'] = TRUE;
          $_SESSION['place'] = $fishing_place;
          $fishing_session_already_started = TRUE;
          $req->closeCursor();
         }
        }
    }

  //Fish form
  if(!isset($_POST['fish']))
  {

  }
  else
  {
    if($_POST['fish_type'] == NULL)
    {

    }
    else if ($_POST['fish_bait'] == NULL)
    {

    }
    else if ($_POST['fish_time'] == NULL)
    {

    }
    else
    {
      $fish_made = TRUE;
      //Insert fish into table fish_list
      $fish_type = $_POST['fish_type'];
      $fish_bait = $_POST['fish_bait'];
      $fish_time = $_POST['fish_time'];
      $fish_weight = $_POST['fish_weight'];
      //$fish_kilos = $_POST['fish_kilos']; Suppression des colonne kilos et gram dans la base de donnees le 05/04
      //$fish_gram = $_POST['fish_gram'];
      $members = $_SESSION['id'];
      // Database Connexion
      try
      {
        $bdd = new PDO('mysql:host=localhost;dbname=journal;charset=utf8', 'root', 'root', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
      }
      catch (exception $e)
      {
        die ('Erreur:' . $e->getMessage());
      }

      //Save the fishing_session ID
      $recherche= $bdd->prepare('SELECT id FROM fishing_session WHERE id_members = :id_members AND end_date IS NULL LIMIT 1');
      $recherche->execute(array(
        'id_members' => $members));

      $fishing_session_id = $recherche->fetch();

      if($fishing_session_id == NULL)
      {
          $error_fishing_session = 'Vous n\'avez pas de session en cour !';
      }
      else {
        //Insert fish row in table
        $add_fish = $bdd->prepare('INSERT INTO fish_list(fish_type, fish_bait, fish_time, id_members, id_fishing_session, fish_weight)
                                   VALUES (:fish_type, :fish_bait, :fish_time, :id_members, :id_fishing_session, :fish_weight)');
        $add_fish->execute(array(
          'fish_type' => $fish_type,
          'fish_bait' => $fish_bait,
          'fish_time' => $fish_time,
          'id_members' => $members,
          'id_fishing_session' => $fishing_session_id['id'],
          'fish_weight' => $fish_weight)); //Modif  du 04/04
        $add_fish->closeCursor();

        $_SESSION['fish_already_made'] = TRUE; //Variable pour affichage page Accueil
      }
    }
  }
  //Delete fish from fish_list table
  /*if(isset($_POST['delete_fish']))
  {
    if(!isset($_POST['select_fish']))
    {

    }
    else
    {
      // Database Connexion
      try
      {
        $bdd = new PDO('mysql:host=localhost;dbname=journal;charset=utf8', 'root', 'root', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
      }
      catch (exception $e)
      {
        die ('Erreur:' . $e->getMessage());
      }
      $req = $bdd->prepare('DELETE FROM fish_list WHERE id = :id')
    }
  }
*/



  //fishing_session_end
  //fishing_session_end
  $fishing_session_stop = FALSE;

  if (isset($_POST['fishing_session_end']))
  {
    // Database Connexion
    try
    {
      $bdd = new PDO('mysql:host=localhost;dbname=journal;charset=utf8', 'root', 'root', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }
    catch (exception $e)
    {
      die ('Erreur:' . $e->getMessage());
    }

    //Save the fishing_session ID
    //$recherche = $bdd->query('SELECT id FROM fishing_session WHERE end_date IS NULL LIMIT 1');
    //$fishing_session_id = $recherche->fetch();

    $end_session = $bdd->prepare('UPDATE fishing_session SET end_date = NOW() WHERE id_members = :id_members AND end_date IS NULL LIMIT 1');
    $end_session->execute(array(
      'id_members' => $members));
    $end_session->closeCursor();

    $_SESSION['started'] = FALSE;
    $_SESSION['ended'] = TRUE;
    unset($_SESSION['place']);
    unset($_SESSION['fish_already_made']);
  }
 ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
    <title>CFC - Nouvelle Session</title>
    <link href="cfcaccueilstyle.css" type="text/css" rel="stylesheet"/>
  </head>
  <body>
    <div id="bloc_page">
      <?php
      // header
      include('cfc_header.php');
      ?>
      <section>
        <?php include('cfc_menu.php'); ?>
        <div id="contenu_nouvellesession">
          <div id="nouvellesession_gauche">
              <?php
              if($fishing_session_already_started == FALSE AND empty($_SESSION['started']))
              {

              ?>
                <div id="starter">
                <h4>Prêt pour lancer une session ?</h4>
                <form method="post" action="cfcnouvellesession.php">
                  <label for="place">Préciser le lieu:<input type="text" name="place" id="place" required/><br/></label><br/>
                  <input type="submit" name="fishing_session_start" id="fishing_session_start" value="Démarrer"/>
                </form>
              </div>
            <?php
              }
              else
              {
                echo '<p>Votre session de pêche à bien démarée !</p>';
                if ($_SESSION['started'])
              {
            ?>
            <h3>Quel poisson venez vous de faire ?</h3>
            <form method="post" action="cfcnouvellesession.php" id="fish_form">
              <fieldset>
                <legend align="center">Détail de la prise</legend>
                <label for="fish_type">Type de Poisson:
                  <select name="fish_type" id="fish_type">
                    <option value="Commune">Commune</option>
                    <option value="Miroir">Miroir</option>
                    <option value="Amour-Blanc">Amour-Blanc</option>
                  </select>
                </label>
                <label for="fish_weight">Poids:<input type="number" step="any" name="fish_weight" id="fish_weight" min="0" max="50"/></label>
                <!-- <fieldset>
                  <legend align="center">Poids</legend>
                  <label for="fish_weight">Poids:<input type="number" step="any" name="fish_weight" id="fish_kilos"/></label>
                </fieldset>
                 <input type="number" step="any" name="fish_kilos" id="fish_kilos"/> Kilos
                  <input type="number" step="50" name="fish_gram" id="fish_gram"/> Grammes -->
                <label>Type d'appats:<input type="text" name="fish_bait"/></label><br/>
                <label>Heure de la prise:<input type="time" name="fish_time"/></label><br/>
                <input type="submit" value="Valider" name="fish" id="fish"/>
              </fieldset>
            </form>
            <!-- Button and function 'fishing_session_end' -->
            <h4>Mettre fin à la session</h4>
            <form method="post" action="cfcnouvellesession.php">
              <input type="submit" value="Arrêter" name="fishing_session_end" id="fishing_session_end"/>
            </form>
            <?php
                }
              }
            ?>
          </div>
          <div id="nouvellesession_droit">
            <?php
             if($fishing_session_already_started == FALSE AND empty($_SESSION['started']))
              {

              }
              else {
            ?>

            <table class="fish_table">
              <thead>
                <th colspan="5">Détail de la Session en cour</th>
                <tr>
                  <th>Type de Carpe</th>
                  <th>Appât</th>
                  <th>Poids</th>
                  <th>Heure</th>
                  <th>Méthode</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Database Connexion
                try
                {
                  $bdd = new PDO('mysql:host=localhost;dbname=journal;charset=utf8', 'root', 'root', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                }
                catch (exception $e)
                {
                  die ('Erreur:' . $e->getMessage());
                }

                //Save the fishing_session ID
                $recherche= $bdd->prepare('SELECT id FROM fishing_session WHERE id_members = :id_members AND end_date IS NULL LIMIT 1');
                $recherche->execute(array(
                  'id_members' => $members));

                $fishing_session_id = $recherche->fetch();

                $req = $bdd->prepare('SELECT id, fish_type, fish_bait, DATE_FORMAT(fish_time, \'%Hh%i\') AS fish_time, fish_weight
                FROM fish_list WHERE id_fishing_session = :id_fishing_session AND id_members = :id_members');
                $req->execute(array(
                  'id_fishing_session' => $fishing_session_id['id'],
                  'id_members' => $members));


                  while($donnees = $req->fetch())
                  {
                    //Modif du 4/04 au sujet de fish weight
                    echo '<tr>
                          <td>' . $donnees['fish_type'] . '</td>
                          <td>' . $donnees['fish_bait'] . '</td>
                          <td>' . $donnees['fish_weight'] . ' Kg</td>
                          <td>' . $donnees['fish_time'] . '</td>
                          <td></td>
                          <td id="select_button"><form method="POST" action="cfcnouvellesession.php"><input type="checkbox" name="select_fish"/></form></td>
                          </tr>';
                  }
                  ?>
                    </tbody>
                  </table>
                  <form methode="POST" action="cfcnouvellesession.php">
                    <label for="delete_fish">Supprimer un poisson:<input type="submit" name="delete_fish" value="Effacer"/></label>
                  </form>
                  <?php
                      echo $_POST['select_fish'];


                  if(!empty($_SESSION['started']) AND !empty($_SESSION['fish_already_made'])) //manip du 05/04 pour affichage total que si poisson fait
                  {
                      $total = $bdd->prepare('SELECT SUM(fish_weight) AS total_weight, COUNT(*) AS total_fish
                      FROM fish_list WHERE id_fishing_session = :id_fishing_session');
                      $total->execute(array(
                        'id_fishing_session' => $fishing_session_id['id']));

                        while($session_total = $total->fetch())
                        {
                          echo '<p>Le poids total est de ' . $session_total['total_weight'] . ' Kilos pour ' . $session_total['total_fish'] . ' poisson(s) !';
                        }
                        $recherche->closeCursor();
                        $req->closeCursor();
                  }
                }
                  ?>

          </div>
        </div>
      </section>
      <footer>
        <h6>Copyright 2017</h6>
      </footer>
    </div>
  </body>
</html>
