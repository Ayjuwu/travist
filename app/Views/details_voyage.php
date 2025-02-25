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

        <!-- Carrousel -->
        <span class="carrousel-span">
            <div class="carrousel">
                <?php foreach ($keypoints as $keypoint): ?>
                    <a href="#" 
                       data-id="<?= $keypoint->id ?>" 
                       data-name="<?= $keypoint->key_point_name ?>" 
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
    let selectedKeypoints = <?= json_encode($current_keypoints ?? []) ?>;
    const keypoints = <?= json_encode($keypoints) ?>;
    let map, markers = [], route = null;

    // Initialisation
    initMap();
    updateSelectedList();
    updateMap();

    // Gestion de la modale
    document.querySelectorAll('.carrousel-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const img = this.querySelector('img');
            document.getElementById('modalImage').src = img.src;
            document.getElementById('imageModal').style.display = 'block';
            currentKeypoint = {
                id: this.dataset.id,
                name: this.dataset.name
            };
        });
    });

    document.getElementById('addToList').addEventListener('click', (e) => {
        e.preventDefault();
        if (currentKeypoint && !selectedKeypoints.includes(currentKeypoint.id)) {
            selectedKeypoints.push(currentKeypoint.id);
            updateSelectedList();
            updateMap();
        }
        closeModal();
    });

    // Fonctions
    function updateSelectedList() {
        const list = document.getElementById('selectedList');
        const input = document.getElementById('selectedKeypoints');
        list.innerHTML = '';
        input.value = selectedKeypoints.join(',');

        selectedKeypoints.forEach(id => {
            const item = document.createElement('div');
            item.className = 'selected-item';
            item.innerHTML = `
                <span>${document.querySelector(`[data-id="${id}"]`).dataset.name}</span>
                <button onclick="removeItem('${id}', event)" class="remove-btn">×</button>
            `;
            list.appendChild(item);
        });

        list.style.display = selectedKeypoints.length ? 'block' : 'none';
    }

    window.removeItem = (id, event) => {
        event.preventDefault();
        selectedKeypoints = selectedKeypoints.filter(itemId => itemId != id);
        updateSelectedList();
        updateMap();
    };

    function initMap() {
        map = L.map('map').setView([48.8566, 2.3522], 4);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
    }

    function updateMap() {
        console.log('Mise à jour de la carte');
        markers.forEach(m => map.removeLayer(m));
        markers = [];
        if (route) map.removeLayer(route);

        const coordinates = [];
        selectedKeypoints.forEach(id => {
            const kp = keypoints.find(k => k.id == id);
            if (kp && kp.key_point_gps_location) {
                const loc = convertToLatLng(kp.key_point_gps_location);
                if (loc) {
                    coordinates.push(loc);
                    markers.push(L.marker(loc).addTo(map));
                } else {
                    console.error('Coordonnées GPS invalides pour le point:', kp);
                }
            } else {
                console.error('Point clé non trouvé ou données GPS manquantes:', id);
            }
        });

        if (coordinates.length >= 2) {
            route = L.polyline(coordinates, { color: '#FF6B6B', weight: 3 }).addTo(map);
        }

        if (coordinates.length) {
            map.fitBounds(L.latLngBounds(coordinates));
        }
    }
    
    function convertToLatLng(gps) {
        if (!gps || typeof gps !== 'string') {
            console.error('Données GPS invalides:', gps);
            return null;
        }
        const [latDMS, lngDMS] = gps.split(/\s*,\s*/);
        return [parseDMS(latDMS), parseDMS(lngDMS)];
    }

    function parseDMS(dms) {
        if (!dms || typeof dms !== 'string') {
            console.error('Données DMS invalides:', dms);
            return null;
        }
        const parts = dms.match(/(\d+)°\s*(\d+)?'?\s*([\d.]+)?/);
        if (!parts) {
            console.error('Format DMS invalide:', dms);
            return null;
        }
        return parseFloat(parts[1]) + 
            (parseFloat(parts[2] || 0) / 60) + 
            (parseFloat(parts[3] || 0) / 3600);
    }

    // Fermeture modale
    const closeModal = () => document.getElementById('imageModal').style.display = 'none';
    document.querySelector('.close-modal').addEventListener('click', closeModal);
    document.addEventListener('click', e => e.target === document.getElementById('imageModal') && closeModal());
});
</script>