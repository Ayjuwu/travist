<div id="travelModal">
    <div class="travel-header">
        <h2><?= esc($current_travel->travel_name) ?></h2>
        <div class="travel-actions">
            <a href="<?= base_url("modifier_un_voyage/$current_travel->id") ?>" class="modify">
                <img src="<?= base_url() ?>pictures/edit-pen.svg" alt="Modifier le voyage">
            </a>
            <a href="<?= base_url("supprimer_un_voyage/$current_travel->id") ?>" class="delete">
                <img src="<?= base_url() ?>pictures/trash-bin-delete.svg" alt="Supprimer le voyage">
            </a>
        </div>
    </div>

    <div class="travel-meta">
        <p><strong>Nombre de personnes :</strong> <?= esc($current_travel->people_number) ?></p>
        <p><strong>Prix individuel :</strong> <?= esc(number_format($current_travel->individual_price, 2)) ?> €</p>
        <p><strong>Prix total :</strong> <?= esc(number_format($current_travel->total_price, 2)) ?> €</p>
        <p><strong>Date de départ :</strong> <?= esc(date('d/m/Y', strtotime($current_travel->travel_start_date))) ?></p>
        <p><strong>Date de retour :</strong> <?= esc(date('d/m/Y', strtotime($current_travel->travel_end_date))) ?></p>
    </div>

    <div class="assigned-keypoints">
        <h4>TRAJET</h4>
        <br>
        <div class="keypoints-list">
            <?php 
                /* $current_keypoints_array = $current_keypoints->toArray();

                // Trier les keypoints par date de début (start_date)
                usort($current_keypoints_array, function($a, $b) {
                    return strtotime($a->pivot->start_date) - strtotime($b->pivot->start_date);
                }); */
            ?>

            <?php foreach ($current_keypoints as $keypoint): ?>
                <div class="keypoint-item">
                    <p><strong>Lieux :</strong> <?= esc($keypoint->key_point_name) ?></p>
                    <p><strong>Durée de visite : </strong><em><?= esc(date('d/m/Y', strtotime($keypoint->pivot->start_date))) ?> - <?= esc(date('d/m/Y', strtotime($keypoint->pivot->end_date))) ?></em></p>
                    <p><strong>Ville :</strong> <?= esc($keypoint->city->city_name) ?></p>
                    <p><strong>Prix total :</strong> <?= esc(number_format($keypoint->key_point_price, 2)) ?> €</p>

                    <?php if($keypoint->is_altered_keypoint) : ?>
                        <p class="alert">Attention ! Ce lieux rencontre actuellement des problèmes de disponibilité !</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Carte -->
    <div id="map" class="travel-map" style="height: 400px; width: 100%;"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let current_keypoints = <?= json_encode($current_keypoints) ?>;
        let map, markers = [], route = null;

        function initMap() {
            map = L.map('map').setView([48.8566, 2.3522], 4); // Centre initial sur Paris
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
        }

        function setRoute() {
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            if (route) map.removeLayer(route);

            const coordinates = [];

            current_keypoints.forEach(current_kp => {
                if (current_kp) {
                    const location = [current_kp['key_point_gps_x'], current_kp['key_point_gps_y']];
                    coordinates.push(location);
                    markers.push(
                        L.marker(location)
                            .addTo(map)
                            .bindPopup(`<b>${current_kp['key_point_name']}</b><br><em>${current_kp['pivot']['start_date']} - ${current_kp['pivot']['end_date']}</em>`)
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

        // Initialisation de la carte
        initMap();
        setRoute();
    });
</script>
