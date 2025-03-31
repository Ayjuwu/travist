<?= \Config\Services::validation()->listErrors() ?>
        <div id="modal">
                <form action="<?php echo base_url() . "modifier_une_ville/modify/$current_city->id"; ?>" method="POST" name="villeForm">
                    <h4 class="title_form"> Modifier un tag </h4>

                    <?= csrf_field() ?>
                    <span>
                        <label for="city_name"> Nom de la ville : </label>
                        <input type="text" name="city_name" id="city_name" value="<?= esc($current_city->city_name) ?>">
                    </span>

                    <span>
                        <label for="city_country"> Nom du pays associé : </label>
                        <input type="text" name="city_country" id="city_country" value="<?= esc($current_city->city_country) ?>">
                    </span>

                    <button type="submit" class="submitBtn" name="submit_city"> Valider </button>
                </form>
            </div>

            <?php if(isset($validation)):?>
                <div class="alert alert-warning">
                <?= $validation->listErrors() ?>
                </div>
            <?php endif;?>
        </body>
    </html>