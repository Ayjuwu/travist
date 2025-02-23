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
            <input type="hidden" name="keypoints" id="selectedKeypoints">

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
     document.addEventListener('DOMContentLoaded', () => {
        let selectedKeypoints = []; // Stocke les IDs des lieux
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
            if (currentKeypoint && !selectedKeypoints.includes(currentKeypoint.id)) {
                selectedKeypoints.push(currentKeypoint.id); // Stocke l'ID
                updateSelectedList();
            }
            closeModal();
        });

        // Mettre à jour la liste et l'input caché
        function updateSelectedList() {
            const list = document.getElementById('selectedList');
            const selectedKeypointsInput = document.getElementById('selectedKeypoints');
            list.innerHTML = '';

            // Met à jour l'input caché avec les IDs séparés par des virgules
            selectedKeypointsInput.value = selectedKeypoints.join(',');

            // Affiche les éléments sélectionnés
            selectedKeypoints.forEach(id => {
                const itemName = document.querySelector(`[data-id="${id}"]`).dataset.name;
                const item = document.createElement('div');
                item.className = 'selected-item';
                item.innerHTML = `
                    <span>${itemName}</span>
                    <button onclick="removeItem('${id}')" class="remove-btn">×</button>
                `;
                list.appendChild(item);
            });

            if (selectedKeypoints.length > 0) {
                list.style.display = 'block';
            } else {
                list.style.display = 'none';
            }
        }

        // Fonction pour retirer un lieu de la liste
        window.removeItem = function(id) {
            selectedKeypoints = selectedKeypoints.filter(itemId => itemId !== id);
            updateSelectedList();
        };

        // Fermeture de la modale
        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
            currentKeypoint = null;
        }

        // Variables pour la recherche
        const searchInput = document.getElementById('searchInput');
        let allCarrouselItems = document.querySelectorAll('.carrousel-item');

        // Fonction de filtrage
        function filterCarrousel(searchTerm) {
            searchTerm = searchTerm.toLowerCase().trim();
            
            allCarrouselItems.forEach(item => {
                const itemName = item.dataset.name.toLowerCase();
                const isVisible = itemName.includes(searchTerm);
                
                item.style.display = isVisible ? 'block' : 'none';
            });

            // Gestion de l'animation
            const hasResults = Array.from(allCarrouselItems).some(item => item.style.display !== 'none');
            carrousel.style.animationPlayState = hasResults ? 'paused' : 'running';
        }

        // Écouteur d'événement pour la recherche
        searchInput.addEventListener('input', (e) => {
            filterCarrousel(e.target.value);
        });

        // Réinitialiser après suppression
        window.removeItem = function(id) {
            selectedKeypoints = selectedKeypoints.filter(itemId => itemId !== id);
            updateSelectedList();
            filterCarrousel(searchInput.value); // Rafraîchir le filtre après suppression
        };
    });
</script>