<?= \Config\Services::validation()->listErrors() ?>

<div id="modal">
    <form action="<?= base_url('modifier_un_voyage/modify/' . $current_travel->id) ?>" method="POST" name="travelForm" id="travelForm">
        <h4 class="title_form">Modifier votre voyage</h4>
        <?= csrf_field() ?>

        <!-- Affichage des prix -->
        <div class="price-summary">
            <p id="individualPrice" class="price">Prix par personne : <?= number_format($current_travel->individual_price, 2) ?> €</p>
            <p id="totalPrice" class="price">Prix total pour tous les voyageurs : <?= number_format($current_travel->total_price, 2) ?> €</p>
        </div>
        
        <span class="form-group">
            <label for="travel_name">Nom du voyage :</label>
            <input type="text" name="travel_name" id="travel_name" value="<?= esc($current_travel->travel_name) ?>">
        </span>
            
        <span class="form-group">
            <label for="people_number">Nombre de voyageurs :</label>
            <input type="text" name="people_number" id="people_number" value="<?= esc($current_travel->people_number) ?>">
            <input type="hidden" name="userID" value="<?= esc($current_travel->user_id) ?>">
        </span>

        <span class="search-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Rechercher un lieu..." class="search-input">
            </div>
        </span>

        <!-- Modale de sélection (pour ajouter un nouveau lieu) -->
        <div id="imageModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <p id="kpName" class="data"></p>
                <br>
                <img id="modalImage" class="modal-image">
                <br>
                <span class="datas-box">
                    <p id="kpCity" class="data"></p>
                    <p id="kpStartDate" class="data"></p>
                    <p id="kpEndDate" class="data"></p>
                    <p id="kpPrice" class="data"></p>
                </span>
                <span class="modal-buttons">
                    <button type="button" id="addToList" class="modal-btn add-btn">Ajouter à la liste</button>
                </span>
            </div>
        </div>

        <!-- Liste des destinations pré-remplie avec les lieux liés au voyage -->
        <div id="selectedList" class="selected-list"></div>

        <!-- Stockage des IDs (si besoin) -->
        <div id="selectedKeypointsContainer"></div>
        
        <!-- Carrousel pour ajouter d'autres lieux -->
        <span class="carrousel-span">
            <div class="carrousel">
                <?php foreach ($keypoints as $keypoint):
                    $city = $keypoint->city()->first();
                    $cityName = $city ? $city->city_name : '';
                ?>
                    <div class="carrousel-item" 
                         data-id="<?= $keypoint->id ?>" 
                         data-name="<?= esc($keypoint->key_point_name) ?>" 
                         data-price="<?= $keypoint->key_point_price ?>" 
                         data-startdate="<?= $keypoint->key_point_start_date ?>" 
                         data-enddate="<?= $keypoint->key_point_end_date ?>" 
                         data-city="<?= esc($cityName) ?>" 
                         data-x="<?= $keypoint->key_point_gps_x ?>" 
                         data-y="<?= $keypoint->key_point_gps_y ?>">
                        <img src="data:image/jpeg;base64,<?= $keypoint->key_point_cover ?>" alt="<?= esc($keypoint->key_point_name) ?>" class="carrousel-image">
                    </div>
                <?php endforeach; ?>
            </div>
        </span>

        <!-- Carte -->
        <div id="map" style="height: 400px; width: 100%;"></div>
      
        <button type="submit" class="submitBtn" name="submit_travel">Valider</button>
    </form>
</div>

<?php if(isset($validation)): ?>
    <div class="alert alert-warning">
        <?= $validation->listErrors() ?>
    </div>
<?php endif; ?>

<!-- Script JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialiser la liste des lieux déjà associés au voyage avec la propriété "preFilled" à true
        let keypoints = <?php 
            echo json_encode(array_map(function($kp) {
                return [
                    'id' => $kp['id'],
                    'name' => $kp['key_point_name'],
                    'price' => $kp['key_point_price'],
                    'startDate' => $kp['pivot']['start_date'],
                    'endDate' => $kp['pivot']['end_date'],
                    'key_point_gps_x' => $kp['key_point_gps_x'],
                    'key_point_gps_y' => $kp['key_point_gps_y'],
                    'city' => isset($kp['city']['city_name']) ? $kp['city']['city_name'] : '',
                    'preFilled' => true
                ];
            }, $current_keypoints->toArray()));
        ?>;
        // Liste complète de tous les keypoints disponibles
        let allKeypoints = <?= json_encode($keypoints) ?>;
        let currentKeypoint = null;
        let markers = [];
        let route = null;
        let map;
        let numberOfTravelers = parseInt(document.getElementById('people_number').value, 10) || 1;

        // Fonction pour mettre à jour le prix total et individuel
        function updatePrices() {
            const individualPrice = keypoints.reduce((total, kp) => total + parseFloat(kp.price || 0), 0);
            const totalPrice = individualPrice * numberOfTravelers;
            document.getElementById('individualPrice').innerText = `Prix par personne : ${individualPrice.toFixed(2)}€`;
            document.getElementById('totalPrice').innerText = `Prix total pour tous les voyageurs : ${totalPrice.toFixed(2)}€`;
        }

        // Mise à jour du nombre de voyageurs lors de la modification de l'input
        document.getElementById('people_number').addEventListener('input', function() {
            const value = parseInt(this.value, 10);
            numberOfTravelers = (!isNaN(value) && value > 0) ? value : 1;
            updatePrices();
        });

        function initMap() {
            map = L.map('map').setView([48.8566, 2.3522], 4);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
        }

        function updateMap() {
            if (!map) return;
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            if (route) {
                map.removeLayer(route);
                route = null;
            }
            let coordinates = [];
            keypoints.forEach(kp => {
                let keypoint = allKeypoints.find(k => k.id == kp.id);
                if (keypoint && keypoint.key_point_gps_x && keypoint.key_point_gps_y) {
                    let coord = [parseFloat(keypoint.key_point_gps_x), parseFloat(keypoint.key_point_gps_y)];
                    coordinates.push(coord);
                    let marker = L.marker(coord)
                        .addTo(map)
                        .bindPopup(`<b>${keypoint.key_point_name}</b>`);
                    markers.push(marker);
                }
            });
            if (coordinates.length >= 2) {
                route = L.polyline(coordinates, { color: '#FF6B6B', weight: 3 }).addTo(map);
            }
            if (coordinates.length > 0) {
                map.fitBounds(L.latLngBounds(coordinates));
            } else {
                map.setView([48.8566, 2.3522], 4);
            }
        }

        function updateSelectedList() {
            const list = document.getElementById('selectedList');
            list.innerHTML = '';
            keypoints.forEach(kp => {
                if (kp) {
                    const item = document.createElement('div');
                    item.className = 'selected-item';
                    // Pour les items pré-remplis, on utilise les dates existantes, sinon on laisse vide
                    const startVal = kp.preFilled ? kp.startDate : "";
                    const endVal = kp.preFilled ? kp.endDate : "";
                    item.innerHTML = `
                        <span>${kp.name}</span>
                        <br>
                        <div class="input-item">
                            <label for="start_date${kp.id}">Arrivée :</label>
                            <input type="text" id="start_date${kp.id}" name="start_date[${kp.id}]"
                                placeholder="Début de disponibilité : ${kp.startDate}"
                                class="datepicker full-width"
                                data-start="${kp.startDate}" value="${startVal}" required>
                        </div>
                        <br>
                        <div class="input-item">
                            <label for="end_date${kp.id}">Départ :</label>
                            <input type="text" id="end_date${kp.id}" name="end_date[${kp.id}]"
                                placeholder="Fin de disponibilité : ${kp.endDate}"
                                class="datepicker full-width"
                                data-end="${kp.endDate}" value="${endVal}" required>
                        </div>
                        <input type="hidden" name="keypoints[]" value="${kp.id}">
                        <button onclick="removeItem(${kp.id}, event)" class="remove-btn">×</button>
                    `;
                    list.appendChild(item);
                }
            });
            list.style.display = keypoints.length ? 'block' : 'none';
            updateMap();
            updatePrices();
        }

        window.removeItem = (id, event) => {
            event.preventDefault();
            keypoints = keypoints.filter(kp => kp.id !== id);
            updateSelectedList();
            updateMap();
        };

        document.querySelectorAll('.carrousel-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                currentKeypoint = {
                    id: parseInt(this.dataset.id),
                    name: this.dataset.name,
                    price: parseFloat(this.dataset.price || 0),
                    startDate: this.getAttribute('data-startdate'),
                    endDate: this.getAttribute('data-enddate'),
                    city: this.dataset.city,
                    x: parseFloat(this.dataset.x),
                    y: parseFloat(this.dataset.y),
                    preFilled: false // Nouvel item ajouté n'est pas pré-rempli
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
            if (currentKeypoint && !keypoints.some(kp => kp.id === currentKeypoint.id)) {
                keypoints.push({ ...currentKeypoint });
                updateSelectedList();
            }
            closeModal();
        });

        // Initialisation des datepickers
        const datepickers = document.querySelectorAll('.datepicker');
        datepickers.forEach(input => {
            const startDate = input.getAttribute('data-start');
            const endDate = input.getAttribute('data-end');
            flatpickr(input, {
                minDate: startDate,
                maxDate: endDate,
                dateFormat: "Y-m-d"
            });
        });

        const closeModal = () => document.getElementById('imageModal').style.display = 'none';
        document.querySelector('.close-modal').addEventListener('click', closeModal);
        document.addEventListener('click', e => {
            if (e.target === document.getElementById('imageModal')) closeModal();
        });

        // Gestion de la recherche dans le carrousel
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('.carrousel-item').forEach(item => {
                const itemName = item.getAttribute('data-name').toLowerCase() || '';
                const itemCity = item.getAttribute('data-city').toLowerCase() || '';
                const itemCountry = item.getAttribute('data-country').toLowerCase() || '';
                if (itemName.includes(term) || itemCity.includes(term) || itemCountry.includes(term)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Fonction pour formater une date au format YYYY-MM-DD
        function formatDate(dateString) {
            const date = new Date(dateString);
            if (isNaN(date)) return '';
            return date.toISOString().split('T')[0];
        }

        // Initialisation
        initMap();
        updateSelectedList();
        updateMap();
        updatePrices();
    });
</script>

