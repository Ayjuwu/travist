<?= \Config\Services::validation()->listErrors() ?>

<div id="modal">
    <form action="<?= base_url('modifier_un_voyage/modify/' . $current_travel->id) ?>" method="POST" name="travelForm" id="travelForm">
        <h4 class="title_form">Modifier votre voyage</h4>
        <?= csrf_field() ?>

        <!-- Affichage des prix -->
        <div class="price-summary">
            <p id="individualPrice" class="price">Prix par personne : <?= number_format(esc($current_travel->individual_price, 2)) ?> €</p>
            <p id="totalPrice" class="price">Prix total pour tous les voyageurs : <?= number_format(esc($current_travel->total_price, 2)) ?> €</p>
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

        <!-- Modale de sélection (pour ajouter un nouveau lieu) -->
        <div id="imageModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <p id="kpName" class="data"></p>
                <br>
                <img id="modalImage" class="modal-image" alt="Image du lieu">
                <br>
                <span class="datas-box">
                    <p id="kpCity" class="data"></p>
                    <p id="kpStartDate" class="data"></p>
                    <p id="kpEndDate" class="data"></p>
                    <p id="kpPrice" class="data"></p>
                    <p id="kpTags" class="data"></p>
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
            <button type="button" class="carrousel-arrow btn-prev">
                <p>‹</p>
            </button>
            <div class="carrousel-container">
                <div class="carrousel">
                <?php 
                    foreach ($keypoints as $keypoint) {
                        if ($keypoint->is_altered_keypoint == 1) {
                            continue; // On saute ce keypoint s'il est marqué comme altéré
                        }
                        
                        $city = $keypoint->city()->first();
                        $cityName = $city ? $city->city_name : '';
                        $cityCountry = $city ? $city->city_country : '';
                        // Récupération des tags sous forme de chaîne séparée par des virgules
                        $tags = $keypoint->tags()->pluck('tag_name')->toArray();
                        $tagsString = implode(', ', $tags);
                        
                        echo "<div class='carrousel-item' 
                                    data-id='{$keypoint->id}' 
                                    data-name='" . esc($keypoint->key_point_name) . "' 
                                    data-price='" . esc($keypoint->key_point_price) . "' 
                                    data-startdate='" . esc($keypoint->key_point_start_date) . "' 
                                    data-enddate='" . esc($keypoint->key_point_end_date) . "' 
                                    data-city='" . esc($cityName) . "' 
                                    data-country='" . esc($cityCountry) . "'
                                    data-tag='" . esc($tagsString) . "'
                                    data-x='" . esc($keypoint->key_point_gps_x) . "' 
                                    data-y='" . esc($keypoint->key_point_gps_y) . "'>
                                <img src='data:image/jpeg;base64,{$keypoint->key_point_cover}' 
                                    alt='" . esc($keypoint->key_point_name) . "' 
                                    class='carrousel-image'>
                            </div>";
                    } ?>
                </div>
            </div>
            <button type="button" class="carrousel-arrow btn-next">
                <p>›</p>
            </button>
        </span>
      
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
        // Initialiser la liste des lieux déjà liés au voyage (pré-remplie) depuis $current_keypoints
        let selectedKeypoints = <?php 
            echo json_encode(array_map(function($kp) {
                return [
                    'id'            => $kp['id'],
                    'name'          => $kp['key_point_name'],
                    'price'         => $kp['key_point_price'],
                    'startDate'     => $kp['pivot']['start_date'],
                    'endDate'       => $kp['pivot']['end_date'],
                    'key_point_gps_x' => $kp['key_point_gps_x'],
                    'key_point_gps_y' => $kp['key_point_gps_y'],
                    'city'          => isset($kp['city']['city_name']) ? $kp['city']['city_name'] : '',
                    'preFilled'     => true
                ];
            }, $current_keypoints->toArray()));
        ?>;
        
        // Tous les lieux disponibles (pour le carrousel)
        let allKeypoints = <?php echo json_encode($keypoints->map(function($kp) {
            $city = $kp->city()->first();
            return [
                'id'            => $kp->id,
                'name'          => esc($kp->key_point_name),
                'price'         => esc($kp->key_point_price),
                'startDate'     => esc($kp->key_point_start_date),
                'endDate'       => esc($kp->key_point_end_date),
                'key_point_gps_x' => esc($kp->key_point_gps_x),
                'key_point_gps_y' => esc($kp->key_point_gps_y),
                'city'          => esc($city) ? esc($city->city_name) : '',
                'tag'           => esc(implode(', ', $kp->tags()->pluck('tag_name')->toArray()))
            ];
        })->toArray()); ?>;
        
        // Pour manipuler la liste des lieux sélectionnés dans le formulaire
        let keypoints = selectedKeypoints.slice();
        let currentKeypoint = null;
        let numberOfTravelers = parseInt(document.getElementById('people_number').value, 10) || 1;

        // Fonction pour mettre à jour les prix
        function updatePrices() {
            const individualPrice = keypoints.reduce((total, kp) => total + parseFloat(kp.price || 0), 0);
            const totalPrice = individualPrice * numberOfTravelers;
            document.getElementById('individualPrice').innerText = `Prix par personne : ${individualPrice.toFixed(2)}€`;
            document.getElementById('totalPrice').innerText = `Prix total pour tous les voyageurs : ${totalPrice.toFixed(2)}€`;
        }

        // Met à jour le nombre de voyageurs dès que l'input change
        document.getElementById('people_number').addEventListener('input', function() {
            const value = parseInt(this.value, 10);
            numberOfTravelers = (!isNaN(value) && value > 0) ? value : 1;
            updatePrices();
        });

        function updateSelectedList() {
            const list = document.getElementById('selectedList');
            list.innerHTML = '';
            keypoints.forEach(kp => {
                if (kp) {
                    const item = document.createElement('div');
                    item.className = 'selected-item';
                    // Pour les items pré-remplis, utiliser leurs dates; pour les nouveaux, laisser vide.
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
            updatePrices();
            updateCarrouselVisibility();
        }

        function updateCarrouselVisibility() {
            document.querySelectorAll('.carrousel-item').forEach(item => {
                const kpId = parseInt(item.dataset.id);
                const isSelected = keypoints.some(kp => kp.id === kpId);
                item.style.display = isSelected ? 'none' : 'block';
            });
        }

        window.removeItem = (id, event) => {
            event.preventDefault();
            keypoints = keypoints.filter(kp => kp.id !== id);
            updateSelectedList();
        };

        // Gestion du carrousel : écoute des clics sur les items
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
                    tag: this.dataset.tag || '',
                    preFilled: false // Nouvel item ajouté n'est pas pré-rempli
                };
                document.getElementById('kpName').innerText = currentKeypoint.name;
                document.getElementById('kpCity').innerText = 'Ville : ' + currentKeypoint.city;
                document.getElementById('kpStartDate').innerText = 'Date de début de disponibilité : ' + currentKeypoint.startDate;
                document.getElementById('kpEndDate').innerText = 'Date de fin de disponibilité : ' + currentKeypoint.endDate;
                document.getElementById('kpPrice').innerText = 'Prix par personne (TTC) : ' + currentKeypoint.price + '€';
                document.getElementById('kpTags').innerText = 'Tags : ' + (currentKeypoint.tag || '#NaN');
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

        function formatDate(dateString) {
            const date = new Date(dateString);
            if (isNaN(date)) return '';
            return date.toISOString().split('T')[0];
        }

        updateSelectedList();
        updatePrices();
    });
</script>
<script src="<?=base_url('assets/carrousel.js')?>"></script>


