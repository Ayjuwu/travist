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

                <!-- Modale de sélection -->
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

                <!-- Liste des destinations -->
                <div id="selectedList" class="selected-list"></div>

                <!-- Stockage des IDs -->
                <div id="selectedKeypointsContainer"></div>
                
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

        <?php if(isset($validation)):?>
            <div class="alert alert-warning">
            <?= $validation->listErrors() ?>
            </div>
        <?php endif;?>
    </body>
</html>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Attente du chargement complet du DOM avant d’exécuter le script
    document.addEventListener('DOMContentLoaded', () => {
        
        // Liste des lieux sélectionnés par l'utilisateur
        let keypoints = [];

        // Liste de tous les lieux disponibles côté serveur, convertis en JSON côté client
        let allKeypoints = <?= json_encode($keypoints->map(function($kp) {
            $city = $kp->city()->first();
            return [
                'id'            => $kp->id,
                'name'          => esc($kp->key_point_name),           
                'price'         => esc($kp->key_point_price),         
                'startDate'     => esc($kp->key_point_start_date),     
                'endDate'       => esc($kp->key_point_end_date),       
                'city'          => $city ? esc($city->city_name) : '', 
                'tag'           => esc(implode(', ', $kp->tags()->pluck('tag_name')->toArray())) 
            ];
        })->toArray()); ?>;

        // Lieux actuellement sélectionné dans le carrousel (pour l'ajout)
        let currentKeypoint = null;

        // Nombre de voyageurs, récupéré dans le champ et converti en entier (par défaut 1)
        let numberOfTravelers = parseInt(document.getElementById('people_number').value, 10) || 1;

        // On met à jour l'affichage du prix total et du prix individuel
        function updatePrices() {
            const individualPrice = keypoints.reduce((sum, kp) => sum + parseFloat(kp.price || 0), 0);
            document.getElementById('individualPrice').innerText =
                `Prix par personne : ${individualPrice.toFixed(2)}€`;
            document.getElementById('totalPrice').innerText =
                `Prix total pour tous les voyageurs : ${(individualPrice * numberOfTravelers).toFixed(2)}€`;
        }

        // Écoute les changements dans le champ du nombre de voyageurs pour update les prix
        document.getElementById('people_number').addEventListener('input', e => {
            const v = parseInt(e.target.value, 10);
            numberOfTravelers = (v > 0 ? v : 1);
            updatePrices();
        });

        // Met à jour la liste des lieux sélectionnés dans l’interface
        function updateSelectedList() {
            const list = document.getElementById('selectedList');
            list.innerHTML = ''; // Réinitialise la liste

            // Si aucun lieu n’est sélectionné, on cache la liste et le carrousel
            if (!keypoints.length) {
                list.style.display = 'none';
                updateCarrouselVisibility();
                return;
            }

            // Pour chaque lieu sélectionné
            keypoints.forEach(kp => {
                const item = document.createElement('div');
                item.className = 'selected-item';

                // Construction de l’item du lieu
                item.innerHTML = `
                    <span>${kp.name}</span>
                    <div class="input-item">
                    <input
                        type="text"
                        id="dr${kp.id}"
                        class="datepicker-range full-width"
                        placeholder="${kp.startDate} ⇆ ${kp.endDate}"
                        readonly
                        required>
                    <input type="hidden" name="start_date[${kp.id}]" id="hs${kp.id}">
                    <input type="hidden" name="end_date[${kp.id}]"   id="he${kp.id}">
                    <input type="hidden" name="keypoints[]" value="${kp.id}">
                    </div>
                    <button onclick="removeItem(${kp.id}, event)" class="remove-btn">×</button>
                `;

                list.appendChild(item);

                // Initialise le Flatpickr
                flatpickr(item.querySelector('.datepicker-range'), {
                    mode: "range", // Mode plage (début-fin)
                    dateFormat: "Y-m-d", // Format des dates affichées
                    minDate: kp.startDate, // Date minimum sélectionnable
                    maxDate: kp.endDate,   // Date maximum sélectionnable
                    defaultDate: [kp.startDate, kp.endDate], // Dates par défaut affichées

                    // Quand le flatpickr est prêt (affiché) "_" en paramètre car valeurs pas utilisées 
                    onReady: function(_, __, inst) {
                        // On remplit les champs cachés avec les dates par défaut
                        document.getElementById(`hs${kp.id}`).value = kp.startDate;
                        document.getElementById(`he${kp.id}`).value = kp.endDate;

                        // On met à jour l'affichage du champ visible
                        inst.input.value = `${kp.startDate} ⇆ ${kp.endDate}`;
                    },

                    // Quand l'utilisateur change les dates sélectionnées
                    onChange: function(selectedDates, _dateStr, inst) {
                        if (selectedDates.length === 2) {
                            // Formate et sauvegarde les nouvelles dates sélectionnées
                            const s = inst.formatDate(selectedDates[0], "Y-m-d");
                            const e = inst.formatDate(selectedDates[1], "Y-m-d");
                            document.getElementById(`hs${kp.id}`).value = s;
                            document.getElementById(`he${kp.id}`).value = e;
                            inst.input.value = `${s} ⇆ ${e}`;
                        }
                    }
                });
            });

            list.style.display = 'block'; // Affiche la liste
            updatePrices(); // On met à jour les prix
            updateCarrouselVisibility(); // On cache les éléments du carrousel déjà sélectionnés
        }

        // Supprime un keypoint de la sélection
        window.removeItem = (id, event) => {
            event.preventDefault();
            keypoints = keypoints.filter(kp => kp.id !== id); // Retire le keypoint
            updateSelectedList(); // Rafraîchit la liste affichée
            updateMap(); // Met à jour la carte (si présente)
        };

        // Affiche/masque les éléments du carrousel en fonction des keypoints sélectionnés
        function updateCarrouselVisibility() {
            document.querySelectorAll('.carrousel-item').forEach(item => {
                const kpId = parseInt(item.dataset.id, 10);
                const isSelected = keypoints.some(kp => kp.id === kpId);
                item.style.display = isSelected ? 'none' : 'inline-block';
            });
        }

        // Ajoute un écouteur à chaque élément du carrousel
        document.querySelectorAll('.carrousel-item').forEach(item => {
            item.addEventListener('click', e => {
                e.preventDefault();
                // Stocke les infos du keypoint cliqué dans currentKeypoint
                currentKeypoint = {
                    id: parseInt(item.dataset.id, 10),
                    name: item.dataset.name,
                    price: parseFloat(item.dataset.price || 0),
                    startDate: item.dataset.startdate,
                    endDate: item.dataset.enddate,
                    city: item.dataset.city,
                    tag: item.dataset.tag,
                    preFilled: false
                };

                // Affiche les infos du keypoint dans la modale
                document.getElementById('kpName').innerText = currentKeypoint.name;
                document.getElementById('kpCity').innerText = 'Ville : ' + currentKeypoint.city;
                document.getElementById('kpStartDate').innerText = 'Début dispo : ' + currentKeypoint.startDate;
                document.getElementById('kpEndDate').innerText = 'Fin dispo : ' + currentKeypoint.endDate;
                document.getElementById('kpPrice').innerText = 'Prix : ' + currentKeypoint.price + '€';
                document.getElementById('kpTags').innerText = 'Tags : ' + (currentKeypoint.tag || '');
                document.getElementById('modalImage').src = item.querySelector('img').src;
                document.getElementById('imageModal').style.display = 'block';
            });
        });

        // Ajoute un keypoint à la liste sélectionnée depuis la modale
        document.getElementById('addToList').addEventListener('click', e => {
            e.preventDefault();
            // Ajoute seulement si ce keypoint n’est pas déjà sélectionné
            if (currentKeypoint && !keypoints.some(kp => kp.id === currentKeypoint.id)) {
                keypoints.push({ ...currentKeypoint }); // Clone et ajoute
                updateSelectedList(); // Met à jour la liste affichée
            }
            // Ferme la modale
            document.getElementById('imageModal').style.display = 'none';
        });

        // Gestion de la fermeture de la modale
        const closeModal = () => document.getElementById('imageModal').style.display = 'none';

        document.querySelector('.close-modal').addEventListener('click', closeModal);
        document.addEventListener('click', e => {
            if (e.target === document.getElementById('imageModal')) closeModal();
        });

        // Initialisation de l'affichage au chargement de la page
        updateSelectedList();
        updatePrices();
    });
</script>
<script src="<?=base_url('assets/carrousel.js')?>"></script>

