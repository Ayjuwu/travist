<?= \Config\Services::validation()->listErrors() ?>

<?php if (isset($errors) && !empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div id="modal">
    <form action="<?php echo base_url() . 'createTravel'; ?>" method="POST" name="travelForm">
        <h4 class="title_form">Planifier un nouveau voyage</h4>
        <?= csrf_field() ?>

        <!-- Affichage des prix -->
        <div class="price-summary">
            <p id="individualPrice" class="price"> Prix par personne : 0€ </p>
            <p id="totalPrice" class="price"> Prix total pour tous les voyageurs : 0€ </p>
        </div>
        
        <span>
            <label for="travel_name">Nom du voyage :</label>
            <input type="text" name="travel_name" id="travel_name" value="<?= set_value('travel_name') ?>">
        </span>
            
        <span>
            <label for="people_number">Nombre de voyageurs :</label>
            <input type="text" name="people_number" id="people_number" value="<?= set_value('people_number') ?>">
            <input type="hidden" name="userID" id="userID" value="<?= set_value('userID', $userID) ?>">
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

        <!-- Liste des destinations -->
        <div id="selectedList" class="selected-list"></div>

        <!-- Stockage des IDs -->
        <div id="selectedKeypointsContainer"></div>
        
        <span class="carrousel-span">
            <div class="carrousel">
                <?php foreach ($keypoints as $keypoint) {
                    // Récupère la ville associée
                    $city = $keypoint->city()->first();
                    $cityName = $city->city_name ?? '';
                    $cityCountry = $city->city_country ?? '';

                    echo "<div class='carrousel-item' 
                            data-id='{$keypoint->id}' 
                            data-name='{$keypoint->key_point_name}' 
                            data-price='{$keypoint->key_point_price}' 
                            data-startDate='{$keypoint->key_point_start_date}' 
                            data-endDate='{$keypoint->key_point_end_date}' 
                            data-city='{$cityName}' 
                            data-country='{$cityCountry}' 
                            data-x='{$keypoint->key_point_gps_x}' 
                            data-y='{$keypoint->key_point_gps_y}'>
                                
                            <img src='data:image/jpeg;base64,{$keypoint->key_point_cover}' 
                                alt='{$keypoint->key_point_name}' 
                                class='carrousel-image'>
                        </div>";
                } ?>
            </div>
        </span>

        <!-- Carte -->
        <div id="map" style="height: 400px; width: 100%;"></div>
      
        <button type="submit" class="submitBtn" name="submit_travel">Valider</button>
    </form>
</div>

<?php if(isset($validation)):?>
    <div class="alert alert-warning">
    <?= $validation->listErrors() ?>
    </div>
<?php endif;?>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let keypoints = []; // Stocke les IDs sélectionnés
        let allKeypoints = <?= json_encode($keypoints) ?>; // Liste complète des keypoints disponibles
        let currentKeypoint = null;
        let markers = []; // Stocke les marqueurs sur la carte
        let route = null; // Stocke la ligne de route tracée
        let map; // Stocke l'objet Leaflet
        let numberOfTravelers = 1; // Nombre de voyageurs par défaut (à ajuster selon la situation)

        // Fonction pour mettre à jour le prix total et individuel
        function updatePrices() {
            const individualPrice = keypoints.reduce((total, kp) => total + parseFloat(kp.price || 0), 0);
            const totalPrice = individualPrice * numberOfTravelers;

            // Mettre à jour l'interface avec les prix
            document.getElementById('individualPrice').innerText = `Prix par personne : ${individualPrice.toFixed(2)}€`;
            document.getElementById('totalPrice').innerText = `Prix total pour tous les voyageurs : ${totalPrice.toFixed(2)}€`;
        }

        // Mise à jour du nombre de voyageurs lorsqu'il est modifié
        document.getElementById('people_number').addEventListener('input', function() {
            // Récupérer la nouvelle valeur
            const value = parseInt(this.value, 10);

            // Vérifier que la valeur est un nombre valide
            if (!isNaN(value) && value > 0) {
                numberOfTravelers = value;
            } else {
                // Si la valeur est invalide, remettre la valeur par défaut (1 voyageur)
                numberOfTravelers = 1;
            }

            // Mettre à jour les prix
            updatePrices();
        });

        function initMap() {
            map = L.map('map').setView([48.8566, 2.3522], 4);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
        }

        function updateMap() {
            if (!map) return; // Vérifier que la carte est bien initialisée

            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            if (route) {
                map.removeLayer(route);
                route = null;
            }

            let coordinates = [];

            keypoints.forEach(kp => {
                let keypoint = allKeypoints.find(k => k.id == kp.id || k.id == kp);
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

                    item.innerHTML = `
                        <span>${kp.name}</span>
                        <br>
                        <div class="input-item">
                            <label for="start_date${kp.id}">Arrivée :</label>
                            <input type="text" id="start_date${kp.id}" name="start_date[${kp.id}]"
                            placeholder="Début de disponibilité : ${kp.startDate}"
                            class="datepicker full-width"
                            data-start="${kp.startDate}" required>
                        </div>

                        <br>

                        <div class="input-item">
                            <label for="end_date${kp.id}">Départ :</label>
                            <input type="text" id="end_date${kp.id}" name="end_date[${kp.id}]"
                            placeholder="Fin de disponibilité : ${kp.endDate}"
                            class="datepicker full-width"
                            data-end="${kp.endDate}" required>
                        </div>

                        <input type="hidden" name="keypoints[]" value="${kp.id}">
                        <button onclick="removeItem(${kp.id}, event)" class="remove-btn">×</button>
                    `;

                    list.appendChild(item);
                }
            });

            list.style.display = keypoints.length ? 'block' : 'none';
            updateMap();
            updatePrices(); // Mettre à jour les prix lorsque la liste change
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
                    price: this.dataset.price,
                    startDate: this.getAttribute('data-startdate'),
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

            if (currentKeypoint && !keypoints.some(kp => kp.id === currentKeypoint.id)) {
                keypoints.push({ ...currentKeypoint }); // Copie pour éviter toute référence étrange
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
                minDate: startDate,  // Date de début disponible
                maxDate: endDate,    // Date de fin disponible
                dateFormat: "Y-m-d", // Format de la date (ex. 2025-03-25)
            });
        });

        const closeModal = () => document.getElementById('imageModal').style.display = 'none';
        document.querySelector('.close-modal').addEventListener('click', closeModal);
        document.addEventListener('click', e => {
            if (e.target === document.getElementById('imageModal')) closeModal();
        });

        // Gestion de la recherche
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();

            document.querySelectorAll('.carrousel-item').forEach(item => {
                const itemName = item.getAttribute('data-name').toLowerCase() || '';
                const itemCity = item.getAttribute('data-city').toLowerCase() || '';
                const itemCountry = item.getAttribute('data-country').toLowerCase() || '';

                // On compare la chaîne de recherche avec le nom, la ville et le pays
                if (
                    itemName.includes(term) ||
                    itemCity.includes(term) ||
                    itemCountry.includes(term)
                ) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Fonction pour formater la date au format YYYY-MM-DD
        function formatDate(dateString) {
            const date = new Date(dateString);
            if (isNaN(date)) return ''; // Vérification si la date est invalide
            return date.toISOString().split('T')[0]; // Retourne YYYY-MM-DD
        }

        // Initialisation
        initMap();
        updateSelectedList();
        updateMap();
    });
</script>
