<?= \Config\Services::validation()->listErrors() ?>

<div id="modal">
    <form action="<?= base_url('modifier_un_voyage/modify/' . $current_travel->id) ?>" method="POST" name="travelForm" id="travelForm">
        <h4 class="title_form">Modifier votre voyage</h4>
        <?= csrf_field() ?>
        
        <span>
            <label for="travel_name">Nom du voyage :</label>
            <input type="text" name="travel_name" id="travel_name" value="<?= $current_travel->travel_name ?>">
        </span>
            
        <span>
            <label for="people_number">Nombre de voyageurs :</label>
            <input type="text" name="people_number" id="people_number" value="<?= $current_travel->people_number ?>">
            <input type="hidden" name="userID" value="<?= $current_travel->user_id ?>">
        </span>

        <span>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Rechercher un lieu..." class="search-input">
            </div>
        </span>

        <!-- Modale de sélection -->
        <div id="imageModal" class="modal" style="display: none;">
            <div class="modal-content">
            <span class="close-modal">&times;</span>
                <p id="kpName" class='data'></p>
                <br>

                <img id="modalImage" class="modal-image">
                <br>

                <span class='datas-box'>
                    <p id="kpCity" class='data'></p>
                    <p id="kpStartDate" class='data'></p>
                    <p id="kpEndDate" class='data'></p>
                    <p id="kpPrice" class='data'></p>
                </span>


                <span class="modal-buttons">
                    <button type="button" id="addToList" class="modal-btn add-btn">Ajouter à la liste</button>
                </span>
            </div>
        </div>

        <!-- Liste sélectionnée -->
        <div id="selectedList" class="selected-list"></div>

        <!-- Stockage des IDs -->
        <div id="selectedKeypointsContainer"></div>

        <!-- Carrousel avec data-attributes -->
        <span class="carrousel-span">
            <div class="carrousel">
                <?php foreach ($keypoints as $keypoint) {
                     echo "<a class='carrousel-item' href='' 
                            data-id='$keypoint->id' 
                            data-name='$keypoint->key_point_name' 
                            data-price='$keypoint->key_point_price' 
                            data-startDate='$keypoint->key_point_start_date' 
                            data-endDate='$keypoint->key_point_end_date' 
                            data-city='" . $keypoint->city()->first()->city_name ."' 
                            data-x='$keypoint->key_point_gps_x' 
                            data-y='$keypoint->key_point_gps_y'>
                             
                            <img src='data:image/jpeg;base64,$keypoint->key_point_cover' 
                                alt='$keypoint->key_point_name' 
                                class='carrousel-image'>
                        </a>";
                } ?>
            </div>
        </span>

        <!-- Carte -->
        <div id="map" style="height: 400px; width: 100%;"></div>
      
        <button type="submit" class="submitBtn" name="submit_travel">Enregistrer</button>
    </form>
</div>

<?php if(isset($validation)): ?>
    <div class="alert alert-warning">
        <?= $validation->listErrors() ?>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let selectedKeypoints = <?= json_encode($current_keypoints) ?>.map(kp => kp.id);
        let map, markers = [], route = null;
        let currentKeypoint = null;

        const getKeypoints = () => {
            return Array.from(document.querySelectorAll('.carrousel-item')).map(node => ({
                id: parseInt(node.dataset.id),
                name: node.dataset.name,
                lat: parseFloat(node.dataset.x),
                lng: parseFloat(node.dataset.y)
            }));
        };

        function initMap() {
            map = L.map('map').setView([48.8566, 2.3522], 4);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
        }

        function updateMap() {
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            if (route) map.removeLayer(route);

            const keypoints = getKeypoints();
            const coordinates = [];

            selectedKeypoints.forEach(id => {
                const kp = keypoints.find(k => k.id === id);
                if (kp) {
                    const location = [kp.lat, kp.lng];
                    coordinates.push(location);
                    markers.push(
                        L.marker(location)
                            .addTo(map)
                            .bindPopup(`<b>${kp.name}</b>`)
                    );
                }
            });

            if (coordinates.length >= 2) {
                route = L.polyline(coordinates, {
                    color: '#FF6B6B',
                    weight: 3,
                    smoothFactor: 1
                }).addTo(map);
            }

            coordinates.length > 0 ?
                map.fitBounds(L.latLngBounds(coordinates)) :
                map.setView([48.8566, 2.3522], 4);
        }

        function updateSelectedList() {
            const list = document.getElementById('selectedList');
            const container = document.getElementById('selectedKeypointsContainer');
            list.innerHTML = ''; // Vider la liste affichée
            container.innerHTML = ''; // Vider le conteneur des inputs cachés

            // Assurer que selectedKeypoints est un tableau d'entiers
            selectedKeypoints = selectedKeypoints.map(Number);

            // Pour chaque ID sélectionné, créer un input hidden et l'ajouter au conteneur
            selectedKeypoints.forEach(id => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'keypoints[]';
                hiddenInput.value = id;
                container.appendChild(hiddenInput);
            });

            // Afficher les lieux sélectionnés dans la liste visible
            selectedKeypoints.forEach(id => {
                const kp = getKeypoints().find(k => k.id === id); // Chercher le lieu par son ID
                if (kp) {
                    const item = document.createElement('div');
                    item.className = 'selected-item';
                    item.innerHTML = `
                        <span>${kp.name}</span>
                        <button onclick="removeItem(${id}, event)" class="remove-btn">×</button>
                    `;
                    list.appendChild(item);
                }
            });

            // Afficher ou masquer la liste en fonction de la taille
            list.style.display = selectedKeypoints.length ? 'block' : 'none';
        }

        window.removeItem = (id, event) => {
            event.preventDefault();

            // Supprimer l'ID de la liste
            selectedKeypoints = selectedKeypoints.filter(itemId => itemId !== id);

            // Mise à jour de la liste affichée et du champ caché
            updateSelectedList();
            updateMap();
        };


        document.querySelectorAll('.carrousel-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                currentKeypoint = {
                    id: parseInt(this.dataset.id),
                    name: this.dataset.name,
                    price: this.dataset.price,
                    startDate: this.getAttribute('data-startdate'), // getAttribute pour gérer la casse
                    endDate: this.getAttribute('data-enddate'),
                    city: this.dataset.city,
                    x: parseFloat(this.dataset.x),
                    y: parseFloat(this.dataset.y)
                };

                document.getElementById('kpName').innerText = currentKeypoint.name;
                document.getElementById('kpCity').innerText = 'Ville : ' + currentKeypoint.city;
                document.getElementById('kpStartDate').innerText = 'Date de début de disponibilité : ' + currentKeypoint.startDate;
                document.getElementById('kpEndDate').innerText = 'Date de fin de disponibilité : ' + currentKeypoint.endDate;
                document.getElementById('kpPrice').innerText = 'Prix par personne (TTC) : ' + currentKeypoint.price + '€';

                document.getElementById('modalImage').src = this.querySelector('img').src;
                document.getElementById('imageModal').style.display = 'block';
            });
        });

        document.getElementById('addToList').addEventListener('click', (e) => {
            e.preventDefault();
            if (currentKeypoint && !selectedKeypoints.includes(currentKeypoint.id)) {
                selectedKeypoints = [...selectedKeypoints, currentKeypoint.id];
                updateSelectedList();
                updateMap();
            }
            closeModal();
        });

        const closeModal = () => document.getElementById('imageModal').style.display = 'none';
        document.querySelector('.close-modal').addEventListener('click', closeModal);
        document.addEventListener('click', e => {
            if (e.target === document.getElementById('imageModal')) closeModal();
        });

        // Initialisation
        initMap();
        updateSelectedList();
        updateMap();

        // Gestion de la recherche
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('.carrousel-item').forEach(item => {
                const name = item.dataset.name.toLowerCase();
                item.style.display = name.includes(term) ? 'block' : 'none';
            });
        });
    });
</script>