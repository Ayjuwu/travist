<?= \Config\Services::validation()->listErrors() ?>

<div id="modal">
    <form action="<?= base_url('modifyTravel/' . $current_travel->id) ?>" method="POST" name="travelForm" id="travelForm">
        <h4 class="title_form">Modifier le voyage</h4>
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
                <img id="modalImage" class="modal-image">
                <div class="modal-buttons">
                    <button type="button" id="addToList" class="modal-btn add-btn">Ajouter à la liste</button>
                </div>
            </div>
        </div>

        <!-- Liste sélectionnée -->
        <div id="selectedList" class="selected-list"></div>

        <!-- Stockage des IDs -->
        <input type="hidden" name="keypoints" id="selectedKeypoints" value="<?= implode(',', $current_keypoints) ?>">

        <!-- Carrousel avec data-attributes -->
        <span class="carrousel-span">
            <div class="carrousel">
                <?php foreach ($keypoints as $keypoint): ?>
                    <a href="#" 
                       data-id="<?= $keypoint->id ?>" 
                       data-name="<?= $keypoint->key_point_name ?>" 
                       data-x="<?= $keypoint->key_point_gps_x ?>" 
                       data-y="<?= $keypoint->key_point_gps_y ?>"
                       class="carrousel-item">
                        <img src="data:image/jpeg;base64,<?= $keypoint->key_point_cover ?>" 
                             alt="<?= $keypoint->key_point_name ?>" 
                             class="carrousel-image">
                    </a>
                <?php endforeach; ?>
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
    // Initialisation des données avec conversion numérique stricte
    let selectedKeypoints = <?= json_encode($current_keypoints) ?>.map(id => parseInt(id));
    let map, markers = [], route = null;
    let currentKeypoint = null;

    // Fonction de récupération des keypoints depuis le DOM
    const getKeypoints = () => {
        return Array.from(document.querySelectorAll('.carrousel-item')).map(node => ({
            id: parseInt(node.dataset.id),
            name: node.dataset.name,
            x: parseFloat(node.dataset.x),
            y: parseFloat(node.dataset.y)
        }));
    };

    // Initialisation
    initMap();
    updateSelectedList();
    updateMap();

    // Gestion des clics sur le carrousel
    document.querySelectorAll('.carrousel-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            currentKeypoint = {
                id: parseInt(this.dataset.id),
                name: this.dataset.name,
                x: parseFloat(this.dataset.x),
                y: parseFloat(this.dataset.y)
            };
            document.getElementById('modalImage').src = this.querySelector('img').src;
            document.getElementById('imageModal').style.display = 'block';
        });
    });

    // Gestion de l'ajout à la liste
    document.getElementById('addToList').addEventListener('click', (e) => {
        e.preventDefault();
        if (currentKeypoint && !selectedKeypoints.includes(currentKeypoint.id)) {
            selectedKeypoints = [...selectedKeypoints, currentKeypoint.id]; // Nouveau tableau
            updateSelectedList();
            updateMap();
        }
        closeModal();
    });

    // Fonction de mise à jour de la liste
    function updateSelectedList() {
        const list = document.getElementById('selectedList');
        const input = document.getElementById('selectedKeypoints');
        list.innerHTML = '';
        input.value = selectedKeypoints.join(',');

        selectedKeypoints.forEach(id => {
            const kpElement = document.querySelector(`[data-id="${id}"]`);
            const item = document.createElement('div');
            item.className = 'selected-item';
            item.innerHTML = `
                <span>${kpElement?.dataset.name || 'Inconnu'}</span>
                <button onclick="removeItem(${id}, event)" class="remove-btn">×</button>
            `;
            list.appendChild(item);
        });

        list.style.display = selectedKeypoints.length ? 'block' : 'none';
    }

    // Fonction de suppression
    window.removeItem = (id, event) => {
        event.preventDefault();
        selectedKeypoints = selectedKeypoints.filter(itemId => itemId !== id);
        updateSelectedList();
        updateMap();
    };

    // Initialisation de la carte
    function initMap() {
        map = L.map('map').setView([48.8566, 2.3522], 4);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
    }

    // Mise à jour de la carte
    function updateMap() {
        // Nettoyage des éléments existants
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
        if (route) map.removeLayer(route);

        // Récupération des données fraîches
        const keypoints = getKeypoints();
        const coordinates = [];

        // Création des marqueurs
        selectedKeypoints.forEach(id => {
            const kp = keypoints.find(k => k.id === id);
            if (kp) {
                const location = [kp.y, kp.x]; // [latitude, longitude]
                coordinates.push(location);
                markers.push(
                    L.marker(location)
                        .addTo(map)
                        .bindPopup(`<b>${kp.name}</b>`)
                );
            }
        });

        // Dessin de l'itinéraire
        if (coordinates.length >= 2) {
            route = L.polyline(coordinates, {
                color: '#FF6B6B',
                weight: 3,
                smoothFactor: 1
            }).addTo(map);
        }

        // Ajustement de la vue
        coordinates.length > 0 ?
            map.fitBounds(L.latLngBounds(coordinates)) :
            map.setView([48.8566, 2.3522], 4);
    }

    // Fermeture de la modale
    const closeModal = () => document.getElementById('imageModal').style.display = 'none';
    document.querySelector('.close-modal').addEventListener('click', closeModal);
    document.addEventListener('click', e => {
        if (e.target === document.getElementById('imageModal')) closeModal();
    });
});
</script>