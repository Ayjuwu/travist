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

            <!-- Conteneur pour la carte -->
            <div id="map" style="height: 400px; width: 100%;"></div>
          
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

        // Convertir les keypoints PHP en JavaScript
        const keypoints = <?php echo json_encode($keypoints); ?>;
        console.log(keypoints); // Vérifiez les données dans la console

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
                updateMap(); // Mettre à jour la carte après l'ajout
            }
            closeModal();
        });

        // Fermeture de la modale lorsque l'utilisateur clique sur la croix
        document.querySelector('.close-modal').addEventListener('click', closeModal);

        // Fermeture de la modale lorsque l'utilisateur clique en dehors de la modale
        document.addEventListener('click', function(event) {
            if (event.target === document.getElementById('imageModal')) {
                closeModal();
            }
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
            updateMap(); // Mettre à jour la carte après la suppression
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

        let map;
        let markers = [];
        let route = null; // Stocke la ligne entre les points

        // Fonction pour initialiser la carte
        function initMap() {
            // Initialiser la carte avec un centre et un zoom par défaut
            map = L.map('map').setView([48.8566, 2.3522], 4); // Centre initial (Paris), zoom level 4

            // Ajouter une couche de tuiles OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
        }

        // Fonction pour ajouter un marqueur
        function addMarker(location) {
            const marker = L.marker(location).addTo(map);
            markers.push(marker);
        }

        // Fonction pour mettre à jour la carte avec les lieux sélectionnés
        function updateMap() {
            console.log("Mise à jour de la carte...");

            // Supprimer les marqueurs et la ligne existante
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            if (route) {
                map.removeLayer(route);
                route = null;
            }

            // Récupérer les coordonnées dans l'ordre de sélection
            const coordinates = [];
            selectedKeypoints.forEach(id => {
                const keypoint = keypoints.find(kp => kp.id === parseInt(id));
                if (keypoint) {
                    const location = convertToLatLng(keypoint.key_point_gps_location);
                    if (location) { // Ignorer les coordonnées invalides
                        coordinates.push(location);
                        addMarker(location);
                    }
                }
            });

            // Tracer une ligne si au moins 2 points
            if (coordinates.length >= 2) {
                route = L.polyline(coordinates, { color: '#FF6B6B', weight: 3 }).addTo(map);
            }

            // Ajuster la vue
            if (coordinates.length > 0) {
                const bounds = L.latLngBounds(coordinates);
                map.fitBounds(bounds);
            }

            selectedKeypoints.forEach(id => {
                const keypoint = keypoints.find(kp => kp.id === parseInt(id));
                if (keypoint) {
                    const location = convertToLatLng(keypoint.key_point_gps_location);
                    console.log("Lieu:", keypoint.key_point_name, "Coordonnées:", location); // <-- Ajoutez cette ligne
                    // ...
                }
            });
        }

        function parseDMS(dmsString) {
            if (!dmsString) return null;
            // Regex améliorée pour capturer degrés, minutes, secondes (optionnelles)
            const parts = dmsString.match(/(\d+)°\s*(\d+)?'?\s*([\d.]+)?/);
            if (!parts) return null;

            const degrees = parseFloat(parts[1]) || 0;
            const minutes = parseFloat(parts[2]) || 0;
            const seconds = parseFloat(parts[3]) || 0;

            return degrees + (minutes / 60) + (seconds / 3600);
        }

        function convertToLatLng(gpsLocation) {
            // Exemple : "48°51'29.988, 2°17'40.012"
            const [latDMS, lngDMS] = gpsLocation.split(/\s*,\s*/);
            const lat = parseDMS(latDMS);
            const lng = parseDMS(lngDMS);

            if (isNaN(lat) || isNaN(lng)) {
                console.error("Coordonnées invalides :", gpsLocation);
                return null;
            }

            console.log("Coordonnées converties :", [lat, lng]);
            return [lat, lng];
        }

        // Initialiser la carte
        initMap();
    });
</script>