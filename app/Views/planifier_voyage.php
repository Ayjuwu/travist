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

            <span>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Rechercher un lieu..." class="search-input">
                </div>
            </span>

            <!-- Ajoutez cette modale pour la sélection -->
            <div id="imageModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <img id="modalImage" class="modal-image">
                    <div class="modal-buttons">
                        <button type="button" id="addToList" class="modal-btn add-btn">Ajouter à la liste</button>
                    </div>
                </div>
            </div>

            <!-- Liste des destinations sélectionnées -->
            <div id="selectedList" class="selected-list"></div>

            <!-- Input caché pour stocker les IDs -->
            <input type="hidden" name="keypoints[]" id="selectedKeypoints" value="<?= set_value('people_number') ?>">

            <!-- Votre carrousel existant -->
            <span class="carrousel-span">
                <div class="carrousel">
                    <?php foreach ($keypoints as $keypoint) {
                        echo "<a href='' 
                                data-id='$keypoint->id' 
                                data-name='$keypoint->key_point_name' 
                                class='carrousel-item'>
                                <img src='data:image/jpeg;base64,$keypoint->key_point_cover' 
                                    alt='$keypoint->key_point_name' 
                                    class='carrousel-image'>
                            </a>";
                    } ?>
                </div>
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
        const searchInput = document.getElementById('searchInput');
        const carrousel = document.querySelector('.carrousel');
        const carrouselItems = document.querySelectorAll('.carrousel-item');

        // Variable pour stocker l'état de l'animation
        let isCarrouselRunning = true;

        // Fonction pour filtrer en temps réel
        function filterCarrousel() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let hasVisibleItems = false;

            carrouselItems.forEach(item => {
                const itemName = item.getAttribute('data-name').toLowerCase();
                const isVisible = itemName.includes(searchTerm);

                item.style.display = isVisible ? 'block' : 'none';

                if (isVisible) {
                    hasVisibleItems = true; // Au moins un élément est visible
                }
            });

            // Arrêter ou redémarrer le carrousel en fonction des résultats
            if (hasVisibleItems && isCarrouselRunning) {
                carrousel.style.animationPlayState = 'paused'; // Arrêter le carrousel
                isCarrouselRunning = false;
            } else if (!searchTerm && !isCarrouselRunning) {
                carrousel.style.animationPlayState = 'running'; // Redémarrer le carrousel
                isCarrouselRunning = true;
            }
        }

        // Écouteur d'événement pour la barre de recherche (en temps réel)
        searchInput.addEventListener('input', filterCarrousel);


        let selectedKeypoints = []; // Tableau pour stocker les noms des lieux
        let currentKeypoint = null;

        // Gestion de l'ouverture de la modale
        document.querySelectorAll('.carrousel-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                currentKeypoint = {
                    id: this.dataset.id,
                    name: this.dataset.name
                };
                document.getElementById('modalImage').src = this.querySelector('img').src;
                document.getElementById('imageModal').style.display = 'block';
            });
        });

        // Gestion du bouton "Ajouter à la liste"
        document.getElementById('addToList').addEventListener('click', function(e) {
            e.preventDefault();
            if (currentKeypoint && !selectedKeypoints.includes(currentKeypoint.name)) {
                selectedKeypoints.push(currentKeypoint.name); // Ajouter le nom du lieu au tableau
                updateSelectedList();
            }
            closeModal();
        });

        const modal = document.getElementById('imageModal');
        const closeModalBtn = document.querySelector('.close-modal');

        // Gestion de l'ouverture de la modale
        document.querySelectorAll('.carrousel-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('modalImage').src = this.querySelector('img').src;
                modal.style.display = 'block';
            });
        });

        // Gestion de la fermeture de la modale
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Fermeture de la modale en cliquant à l'extérieur
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Mettre à jour la liste des lieux choisis
        function updateSelectedList() {
            const list = document.getElementById('selectedList');
            list.innerHTML = ''; // Vider la liste

            // Afficher chaque lieu dans la liste
            selectedKeypoints.forEach(name => {
                const item = document.createElement('div');
                item.className = 'selected-item';
                item.innerHTML = `
                    <span>${name}</span>
                    <button onclick="removeItem('${name}')" class="remove-btn">×</button>
                `;
                list.appendChild(item);
            });

            // Afficher ou masquer la div en fonction du nombre de lieux sélectionnés
            if (selectedKeypoints.length > 0) {
                list.style.display = 'block'; // Afficher la div
            } else {
                list.style.display = 'none'; // Masquer la div
            }

            // Mettre à jour le champ caché avec les noms des lieux
            document.getElementById('selectedKeypointsNames').value = selectedKeypoints.join(',');
        }

        // Fonction pour retirer un lieu de la liste
        window.removeItem = function(name) {
            selectedKeypoints = selectedKeypoints.filter(item => item !== name);
            updateSelectedList();
        };

        // Fermeture de la modale
        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
            currentKeypoint = null;
        }

        // Fermeture de la modale en cliquant à l'extérieur
        window.addEventListener('click', function(e) {
            if (e.target === document.getElementById('imageModal')) {
                closeModal();
            }
        });
    });
</script>