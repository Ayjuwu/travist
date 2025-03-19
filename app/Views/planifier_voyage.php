<?= \Config\Services::validation()->listErrors() ?>

<div id="modal">
    <form action="<?php echo base_url() . 'createTravel'; ?>" method="POST" name="travelForm">
        <h4 class="title_form">Planifier un nouveau voyage</h4>
        <?= csrf_field() ?>
        
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
        <input type="hidden" name="keypoints" id="selectedKeypoints">

        
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
      
        <button type="submit" class="submitBtn" name="submit_travel">Valider</button>
    </form>
</div>

<?php if(isset($validation)):?>
    <div class="alert alert-warning">
    <?= $validation->listErrors() ?>
    </div>
<?php endif;?>

<script>

// On attend que tout le DOM ait fini de charger 
document.addEventListener('DOMContentLoaded', () => {

    // on initialise les valeurs des lieux et de la carte à null (reset)
    let selectedKeypoints = [];
    let currentKeypoint = null;
    let map;
    let markers = [];
    let route = null;

    // On associe les datas html aux données des lieux en php pour le javascript, dont les coordonnées décimales pour la carte
    const keypoints = <?php echo json_encode(array_map(function($kp) {
        return [
            'id' => $kp['id'],
            'x' => (float)$kp['key_point_gps_x'],
            'y' => (float)$kp['key_point_gps_y'],
            'name' => $kp['key_point_name'],
            'price' => $kp['key_point_price'],
            'city' => $kp['city_id'],
            'startDate' => $kp['key_point_start_date'],
            'endDate' => $kp['key_point_end_date'],
        ];
    }, $keypoints->toArray())); ?>;

    // Gestion du clic sur les éléments du carrousel
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

    // Initialisation de la carte
    function initMap() {
        map = L.map('map').setView([48.8566, 2.3522], 4);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
    }

    // Mise à jour de la carte
    function updateMap() {
        // Nettoyage
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
        if (route) map.removeLayer(route);

        // Ajout des nouveaux marqueurs
        const coordinates = [];
        selectedKeypoints.forEach(id => {
            const kp = keypoints.find(k => k.id === id);
            if (kp) {
                const coord = [kp.x, kp.y]; // Conservez cet ordre si x=latitude et y=longitude
                coordinates.push(coord);
                markers.push(L.marker(coord)
                    .addTo(map)
                    .bindPopup(`<b>${kp.name}</b>`));
            }
        });

        // Dessin de l'itinéraire
        if (coordinates.length >= 2) {
            route = L.polyline(coordinates, {color: '#FF6B6B'}).addTo(map);
        }

        // Ajustement de la vue
        coordinates.length > 0 ? 
            map.fitBounds(L.latLngBounds(coordinates)) : 
            map.setView([48.8566, 2.3522], 4);
    }

    // Gestion de l'ajout à la liste
    document.getElementById('addToList').addEventListener('click', () => {
        if (currentKeypoint && !selectedKeypoints.includes(currentKeypoint.id)) {
            selectedKeypoints.push(currentKeypoint.id);
            document.getElementById('selectedKeypoints').value = selectedKeypoints.join(',');
            updateMap();
            updateSelectedList();
        }
        document.getElementById('imageModal').style.display = 'none';
    });

    document.querySelector('.close-modal').addEventListener('click', () => {
        document.getElementById('imageModal').style.display = 'none';
    });

    // Mise à jour de l'affichage de la liste (CORRIGÉ)
    function updateSelectedList() {
        const list = document.getElementById('selectedList');
        const input = document.getElementById('selectedKeypoints');
        list.innerHTML = '';
        input.value = selectedKeypoints.join(',');

        selectedKeypoints.forEach(id => {
            const kp = keypoints.find(k => k.id === id);
            const item = document.createElement('div');
            item.className = 'selected-item';
            item.innerHTML = `
                <span>${kp?.name || 'Lieu inconnu'}</span>
                <button onclick="removeItem(${id})" class="remove-btn">×</button>
            `;
            list.appendChild(item);
        });

        list.style.display = selectedKeypoints.length ? 'block' : 'none';
    }

    // Suppression d'un élément (CORRIGÉ)
    window.removeItem = id => {
        selectedKeypoints = selectedKeypoints.filter(k => k !== id);
        document.getElementById('selectedKeypoints').value = selectedKeypoints.join(',');
        updateSelectedList();
        updateMap();
    };

    // Recherche
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.carrousel-item').forEach(item => {
            const name = item.dataset.name.toLowerCase();
            item.style.display = name.includes(term) ? 'block' : 'none';
        });
    });

    // Initialisation
    initMap();
});
</script>