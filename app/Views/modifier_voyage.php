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
    </body>
</html>

<!-- Script JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Lieux déjà liés au voyage (avec leurs dates de visite dans pivot)
        let selectedKeypoints = <?= json_encode(array_map(function($kp) {
            return [
                'id'                => $kp['id'],
                'name'              => $kp['key_point_name'],
                'price'             => $kp['key_point_price'],
                // Disponibilité initiale du lieu
                'availabilityStart' => $kp['key_point_start_date'],
                'availabilityEnd'   => $kp['key_point_end_date'],
                // Dates que l'utilisateur avait choisies
                'visitStart'        => $kp['pivot']['start_date'],
                'visitEnd'          => $kp['pivot']['end_date'],
                'preFilled'         => true
            ];
        }, $current_keypoints->toArray())); ?>;

        // Tous les lieux pour le carrousel
        let allKeypoints = <?= json_encode($keypoints->map(function($kp) {
            $city = $kp->city()->first();
            return [
                'id'                => $kp->id,
                'name'              => esc($kp->key_point_name),
                'price'             => esc($kp->key_point_price),
                'availabilityStart' => esc($kp->key_point_start_date),
                'availabilityEnd'   => esc($kp->key_point_end_date),
                'city'              => $city ? esc($city->city_name) : '',
                'tag'               => esc(implode(', ', $kp->tags()->pluck('tag_name')->toArray()))
            ];
        })->toArray()); ?>;

        let keypoints = selectedKeypoints.slice();
        let currentKeypoint = null;
        let numberOfTravelers = parseInt(document.getElementById('people_number').value, 10) || 1;

        // On met à jour l'affichage des prix à l'initialisation
        function updatePrices() {
            const sum = keypoints.reduce((acc, kp) => acc + parseFloat(kp.price || 0), 0);
            document.getElementById('individualPrice').innerText =
                `Prix par personne : ${sum.toFixed(2)}€`;
            document.getElementById('totalPrice').innerText =
                `Prix total : ${(sum * numberOfTravelers).toFixed(2)}€`;
        }
        document.getElementById('people_number').addEventListener('input', e => {
            const v = parseInt(e.target.value, 10);
            numberOfTravelers = v > 0 ? v : 1;
            updatePrices();
        });

        // Génère la liste des keypoints sélectionnés + datepickers
        function updateSelectedList() {
            const list = document.getElementById('selectedList');
            list.innerHTML = '';

            if (!keypoints.length) {
                list.style.display = 'none';
                updateCarrouselVisibility();
                return;
            }

            keypoints.forEach(kp => {
                const div = document.createElement('div');
                div.className = 'selected-item';
                div.innerHTML = `
                    <span>${kp.name}</span>
                    <div class="input-item">
                    <!-- Champ unique datepicker en mode range -->
                    <input
                        type="text"
                        id="dr${kp.id}"
                        class="datepicker-range full-width"
                        readonly
                        required>
                    <!-- Cachés pour envoyer au serveur -->
                    <input type="hidden" name="start_date[${kp.id}]" id="hs${kp.id}">
                    <input type="hidden" name="end_date[${kp.id}]"   id="he${kp.id}">
                    <input type="hidden" name="keypoints[]" value="${kp.id}">
                    </div>
                    <button onclick="removeItem(${kp.id}, event)" class="remove-btn">×</button>
                `;
                list.appendChild(div);

                // Initialise Flatpickr
                flatpickr(div.querySelector('.datepicker-range'), {
                    mode: "range",
                    dateFormat: "Y-m-d",
                    minDate: kp.availabilityStart,
                    maxDate: kp.availabilityEnd,

                    // Si pré-rempli, on affiche les dates de visite, sinon la plage dispo
                    defaultDate: kp.preFilled
                        ? [kp.visitStart, kp.visitEnd]
                        : [kp.availabilityStart, kp.availabilityEnd],
                    onReady: function(_, __, inst) {
                        // Remplit les hidden et le champ visible au chargement
                        const s = kp.preFilled ? kp.visitStart : kp.availabilityStart;
                        const e = kp.preFilled ? kp.visitEnd   : kp.availabilityEnd;
                        document.getElementById(`hs${kp.id}`).value = s;
                        document.getElementById(`he${kp.id}`).value = e;
                        inst.input.value = `${s} ⇆ ${e}`;
                    },
                    onChange: function(selectedDates, _dateStr, inst) {
                        if (selectedDates.length === 2) {
                            // Met à jour si l'utilisateur change l'intervalle
                            const s = inst.formatDate(selectedDates[0], "Y-m-d");
                            const e = inst.formatDate(selectedDates[1], "Y-m-d");
                            document.getElementById(`hs${kp.id}`).value = s;
                            document.getElementById(`he${kp.id}`).value = e;
                            inst.input.value = `${s} ⇆ ${e}`;
                        }
                    }
                });
            });
            list.style.display = 'block';
            updatePrices();
            updateCarrouselVisibility();
        }

        // Supprimer un keypoint de la sélection
        window.removeItem = (id, e) => {
            e.preventDefault();
            keypoints = keypoints.filter(kp => kp.id !== id);
            updateSelectedList();
        };

        // Montre/masque les items du carrousel selon la sélection
        function updateCarrouselVisibility() {
            document.querySelectorAll('.carrousel-item').forEach(item => {
                const kpId = parseInt(item.dataset.id, 10);
                const isSel = keypoints.some(kp => kp.id === kpId);
                item.style.display = isSel ? 'none' : '';
            });
        }

        // Clic sur un item du carousel pour ouvrir la modale
        document.querySelectorAll('.carrousel-item').forEach(item => {
            item.addEventListener('click', e => {
                e.preventDefault();
                currentKeypoint = {
                    id: parseInt(item.dataset.id, 10),
                    name: item.dataset.name,
                    price: parseFloat(item.dataset.price||0),
                    availabilityStart: item.dataset.startdate,
                    availabilityEnd:   item.dataset.enddate,
                    city:  item.dataset.city,
                    tag:   item.dataset.tag,
                    preFilled: false
                };

                // Remplir la modale
                document.getElementById('kpName').innerText      = currentKeypoint.name;
                document.getElementById('kpCity').innerText      = 'Ville : ' + currentKeypoint.city;
                document.getElementById('kpStartDate').innerText = 'Début dispo : ' + currentKeypoint.availabilityStart;
                document.getElementById('kpEndDate').innerText   = 'Fin dispo : ' + currentKeypoint.availabilityEnd;
                document.getElementById('kpPrice').innerText     = 'Prix : ' + currentKeypoint.price + '€';
                document.getElementById('kpTags').innerText      = 'Tags : ' + (currentKeypoint.tag||'');
                document.getElementById('modalImage').src        = item.querySelector('img').src;
                document.getElementById('imageModal').style.display = 'block';
            });
        });

        // Bouton "Ajouter à la liste" dans la modale
        document.getElementById('addToList').addEventListener('click', e => {
            e.preventDefault();
            if (currentKeypoint && !keypoints.some(kp => kp.id === currentKeypoint.id)) {
                keypoints.push({ ...currentKeypoint });
                updateSelectedList();
            }
            document.getElementById('imageModal').style.display = 'none';
        });

        // Fermeture de la modale
        const closeModal = () => document.getElementById('imageModal').style.display = 'none';
        document.querySelector('.close-modal').addEventListener('click', closeModal);
        document.addEventListener('click', e => {
            if (e.target === document.getElementById('imageModal')) closeModal();
        });

        // Initialisation au chargement
        updateSelectedList();
        updatePrices();
    });
</script>
<script src="<?= base_url('assets/carrousel.js') ?>"></script>




