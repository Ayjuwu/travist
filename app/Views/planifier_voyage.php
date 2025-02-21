<?= \Config\Services::validation()->listErrors() ?>

    <div id="modal">
        <form action="<?php echo base_url() . 'createTravel'; ?>" method="POST" name="travelForm">
            <h4 class="title_form"> Planifier un nouveau voyage </h4>
            <?= csrf_field() ?>
            <span>
                <label for="travel_name"> Nom du voyage : </label>
                <input type="text" name="travel_name" id="travel_name" value="<?= set_value('travel_name') ?>">
            </span>
                
            <span>
                <label for="people_number"> Nombre de voyageurs : </label>
                <input type="text" name="people_number" id="people_number" value="<?= set_value('people_number') ?>">
                <input type="hidden" name="userID" id="userID" value="<?= set_value('userID', $userID) ?>">
            </span>

            <!-- <div class="carrousel">
                <div class="carrousel-inner">
                </div>
            </div> -->

            <span class="select_box" id="selectBox">
                <label> Choisir un lieux : </label>
                <select name='keypoints[]'> 
                    <option selected disabled hidden> Choisissez un lieux : </option>
                    <?php foreach ($keypoints as $keypoint) :
                        echo "<option value='$keypoint->id'>$keypoint->key_point_name</option>";
                    endforeach; ?>
                </select>
                <button type="button" id="addKpBtn"> + </button>
            </span>
          
            <button type="submit" class="submitBtn" name="submit_travel"> Valider </button>
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
    const addKpBtn = document.getElementById('addKpBtn');
    const selectBox = document.getElementById('selectBox');
    
    const select = document.createElement('select');

    document.addEventListener('DOMContentLoaded', () => {
        addKpBtn.addEventListener('click', () => {
            selectBox.appendChild(select);
            selectBox.insertAdjacentElement('beforeEnd', addKpBtn);
            select.outerHTML = `
                <select name='keypoints[]'>
                    <option selected disabled hidden> Choisissez un lieux : </option>
                    <?php foreach ($keypoints as $keypoint) :
                        echo "<option value='$keypoint->id'>$keypoint->key_point_name</option>";
                    endforeach; ?>
                </select>
            `;
        })

        // const carrouselInner = document.querySelector('.carrousel-inner');
        // const carrouselItems = document.querySelectorAll('.carrousel-item');
        // let currentIndex = 0;

        // function moveToNextSlide() {
        //     currentIndex = (currentIndex + 1) % carrouselItems.length;
        //     updateCarrousel();
        // }

        // function updateCarrousel() {
        //     const offset = -currentIndex * 100;
        //     carrouselInner.style.transform = `translateX(${offset}%)`;
        // }

        // setInterval(moveToNextSlide, 3000); // Change de slide toutes les 3 secondes
    });
    </script>