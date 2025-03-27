<?= \Config\Services::validation()->listErrors() ?>
        <div id="modal">
                <form action="<?php echo base_url() . "modifier_un_tag/modify/$current_tag->id"; ?>" method="POST" name="tagForm">
                    <h4 class="title_form"> Modifier un tag </h4>

                    <?= csrf_field() ?>
                    <span>
                        <label for="tag_name"> Nom du tag : </label>
                        <input type="text" name="tag_name" id="tag_name" value="<?= str_replace('#', '', $current_tag->tag_name) ?>">
                    </span>

                    <button type="submit" class="submitBtn" name="submit_tag"> Valider </button>
                </form>
            </div>

            <?php if(isset($validation)):?>
                <div class="alert alert-warning">
                <?= $validation->listErrors() ?>
                </div>
            <?php endif;?>
        </body>
    </html>