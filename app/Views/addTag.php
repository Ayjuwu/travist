    <?= \Config\Services::validation()->listErrors() ?>
        
        <div id="modal">
                <form action="<?php echo base_url() . 'createTag'; ?>" method="POST" name="keyPointForm">
                    <h4 class="title_form"> Ajoutez un nouveau Tag </h4>

                    <?= csrf_field() ?>
                    <span>
                        <label for="tag_name"> Nom du tag : </label>
                        <input type="text" name="tag_name" id="tag_name" value="<?= set_value('tag_name') ?>">
                    </span>
                        
                    <button type="submit" class="submitBtn" name="submit_key_point"> Valider </button>
                </form>
            </div>

            <?php if(isset($validation)):?>
                <div class="alert alert-warning">
                <?= $validation->listErrors() ?>
                </div>
            <?php endif;?>
        </body>
    </html>
</body>
</html>