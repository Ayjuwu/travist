            <?= \Config\Services::validation()->listErrors() ?>
            
            <div id="modal">
                <form action="<?php echo base_url();?>createAccount" method="POST" name="register">
                    <h4 class="title_form"> Créez votre nouveau compte Travist ! </h4>
                    
                    <?= csrf_field() ?>
                    <span>
                        <label for="user_name"> Nom d'utilisateur : </label>
                        <input type="text" name="user_name" id="user_name" value="<?= set_value('user_name') ?>">
                    </span>

                    <span>
                        <label for="user_email"> Email : </label>
                        <input type="email" name="user_email" id="user_email" value="<?= set_value('user_email') ?>">
                    </span>

                    <span>
                        <label for="user_password"> Mot de passe : </label>
                        <input type="password" name="user_password" id="user_password" value="<?= set_value('user_password') ?>">
                    </span>

                    <span>
                        <label for="confirm_password"> Confirmer le mot de passe : </label>
                        <input type="password" name="confirm_password" id="confirm_password">
                    </span>

                    <button type="submit" class="submitBtn" name="submit_registration"> Enregistrer </button>
                    <a href="<?php echo base_url().'connexion'; ?>" class="link"> Connectez-vous ici </a>
                </form>
            </div>

            <?php if(isset($validation)):?>
                <div class="alert alert-warning">
                <?= $validation->listErrors() ?>
                </div>
            <?php endif;?>
        </body>
    </html>
            
        