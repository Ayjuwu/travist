        <?= \Config\Services::validation()->listErrors() ?>
        
        <div id="modal">
                <form action="<?php echo base_url() . 'createKeyPoint'; ?>" method="POST" name="keyPointForm" enctype="multipart/form-data">
                    <h4 class="title_form"> Ajoutez un nouveau lieu </h4>

                    <?= csrf_field() ?>
                    <span>
                        <label for="key_point_name"> Nom du lieu : </label>
                        <input type="text" name="key_point_name" id="key_point_name" value="<?= set_value('key_point_name') ?>">
                    </span>
                        
                    <span>
                        <label for="key_point_price"> Prix : </label>
                        <input type="text" name="key_point_price" id="key_point_price" value="<?= set_value('key_point_price') ?>">
                    </span>

                    <span>
                        <label for="key_point_start_date"> Mois du début de disponibilité : </label>
                        <input type="text" name="key_point_start_date" id="key_point_start_date" value="<?= set_value('key_point_start_date') ?>">
                    </span>

                    <span>
                        <label for="key_point_end_date"> Mois du fin de disponibilité : </label>
                        <input type="text" name="key_point_end_date" id="key_point_end_date" value="<?= set_value('key_point_end_date') ?>">
                    </span>

                    <span class="select_box" id="selectBoxCities">
                        <label> Choisir une ville : </label>
                        <select id='city' name='city'>
                            <option selected disabled hidden> Choisissez une ville : </option>
                            <?php foreach ($cities as $city) :
                                echo "<option value='$city->id'>" . $city->city_name . "</option>";
                            endforeach; ?>
                        </select>
                    </span>

                    <span>
                        <label for="key_point_cover"> Image du lieu : </label>
                        <input type="file" id="key_point_cover" name="key_point_cover" value="<?= set_value('key_point_cover') ?>" accept="image/png, image/jpeg" />
                    </span>

                    <span>
                        <label for="key_point_gps_x"> Coordonnées X : </label>
                        <input type="text" name="key_point_gps_x" id="key_point_gps_x" value="<?= set_value('key_point_gps_x') ?>">
                    </span>

                    <span>
                        <label for="key_point_gps_y"> Coordonnées Y : </label>
                        <input type="text" name="key_point_gps_y" id="key_point_gps_y" value="<?= set_value('key_point_gps_y') ?>">
                    </span>

                    <span class="select_box" id="selectBoxTags">
                        <label> Choisir un tag : </label>
                        <select id='tags[]' name='tags[]'>
                            <option selected disabled hidden> Choisissez un tag : </option>
                            <?php foreach ($tags as $tag) :
                                echo "<option value='$tag->id'>" . $tag->tag_name . "</option>";
                            endforeach; ?>
                        </select>
                        <button type="button" id="addTagBtn"> + </button>
                    </span>

                    <span>
                        <label for="is_altered_keypoint"> Le lieux rencontre actuellement un problème pour la visite ? </label>
                        <input type="checkbox" name="is_altered_keypoint" id="is_altered_keypoint" value="1">
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
        const selectBox = document.getElementById('selectBoxTags');
        
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

    
