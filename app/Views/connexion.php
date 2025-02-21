            <?= \Config\Services::validation()->listErrors() ?>
            
            <div id="modal">
                <form action="<?php echo base_url();?>loginAccount" method="POST" name="login">
                    <h4 class="title_form"> Connectez-vous à votre compte Travist ! </h4>

                    <?= csrf_field() ?>
                    <span>
                        <label for="user_email"> Email : </label>
                        <input type="email" name="user_email" id="user_email" value="<?= set_value('user_email') ?>">
                    </span>
                        
                    <span>
                        <label for="user_password"> Mot de passe : </label>
                        <input type="password" name="user_password" id="user_password" value="<?= set_value('user_password') ?>">
                    </span>

                    <button type="submit" class="submitBtn" name="submit_login"> Se connecter </button>
                    <a href="<?php echo base_url().'inscription'; ?>" class="link"> Inscrivez-vous ici </a>
                </form>
            </div>

            <?php if(session()->getFlashdata('msg')):?>
                <div class="alert alert-warning">
                <?= session()->getFlashdata('msg') ?>
                </div>
            <?php endif;?>

            </body>
        </html>
            