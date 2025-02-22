<?= \Config\Services::validation()->listErrors() ?>
        <div id="modal">
                <form action="<?php echo base_url() . "modifier_un_lieu/modify/$current_keypoint->id"; ?>" method="POST" name="keyPointForm" enctype="multipart/form-data">
                    <h4 class="title_form"> Modifier un lieu </h4>

                    <?= csrf_field() ?>
                    <span>
                        <label for="key_point_name"> Nom du lieu : </label>
                        <input type="text" name="key_point_name" id="key_point_name" value="<?= $current_keypoint->key_point_name ?>">
                    </span>
                        
                    <span>
                        <label for="key_point_price"> Prix : </label>
                        <input type="text" name="key_point_price" id="key_point_price" value="<?= $current_keypoint->key_point_price ?>">
                    </span>

                    <span>
                        <label for="key_point_start_date"> Mois du début de disponibilité : </label>
                        <input type="text" name="key_point_start_date" id="key_point_start_date" value="<?= $current_keypoint->key_point_start_date ?>">
                    </span>

                    <span>
                        <label for="key_point_end_date"> Mois du début de disponibilité : </label>
                        <input type="text" name="key_point_end_date" id="key_point_end_date" value="<?= $current_keypoint->key_point_end_date ?>">
                    </span>

                    <span>
                        <label for="key_point_nearest_city"> Ville la plus proche : </label>
                        <input type="text" name="key_point_nearest_city" id="key_point_nearest_city" value="<?= $current_keypoint->key_point_nearest_city ?>">
                    </span>

                    <span>
                        <label for="key_point_cover"> Image du lieu : </label>
                        <input type="file" id="key_point_cover" name="key_point_cover" value="<?= set_value('key_point_cover') ?>" accept="image/*" />
                    </span>

                    <span>
                        <label for="key_point_gps_location"> Coordonnées GPS : </label>
                        <input type="text" name="key_point_gps_location" id="key_point_gps_location" value="<?= $current_keypoint->key_point_gps_location ?>">
                    </span>

                    <span class="select_box" id="selectBox">
                        <label> Choisir un tag : </label>
                        <select id='tags[]'name='tags[]'>
                            <option selected disabled hidden> Choisissez un tag : </option>
                            <?php foreach ($tags as $tag) :
                                echo "<option value='$tag->id'>" . $tag->tag_name . "</option>";
                            endforeach; ?>
                        </select>
                        <button type="button" id="addTagBtn"> + </button>
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

    <script>
        const addTagBtn = document.getElementById('addTagBtn');
        const selectBox = document.getElementById('selectBox');
        
        const select = document.createElement('select');

        document.addEventListener('DOMContentLoaded', () => {
            addTagBtn.addEventListener('click', () => {
                selectBox.appendChild(select);
                selectBox.insertAdjacentElement('beforeEnd', addTagBtn);

                select.outerHTML = `
                    <select name='tags[]'>
                        <option selected disabled hidden> Choisissez un tag : </option>
                        <?php foreach ($tags as $tag) :
                            echo "<option value='$tag->id'>$tag->tag_name</option>";
                        endforeach; ?>
                    </select>
                `;
            })
        });
    </script>