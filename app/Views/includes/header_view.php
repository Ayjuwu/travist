<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="<?php echo base_url() . '/assets/style.css'; ?>">
        <title> <?php echo esc($title); ?> </title>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    </head>
    <body>
        <header>
            <h1> Travist </h1>
            <button type="button" class="accountProfile"> <img src="<?php echo base_url().'pictures/profile_user.svg'; ?>" alt="user profile account" class="accountProfileSVG"> </button>

            <div class="accountDiv">
                <a href="<?php echo base_url() . 'connexion/disconnect'; ?>" class="accountBtn"> Déconnexion </a>

                <?php $session = session(); ?>

                <?php if($session->get('isLoggedIn')) {
                    if($session->get('user_name') === 'admin') {
                        echo "<a href=" . base_url() . "panel_administrateur class='accountBtn'> Retour au panel </a>";
                    } else {
                        echo "<a href=" . base_url() . "profil class='accountBtn'> Retour au profil </a>";
                    }
                }?>
            </div>
        </header>

