<div id="modal">
    <a href='<?= base_url() ."profil/modifier_un_voyage/$current_travel->id" ?>' class='modify'><img src='<?= base_url() ?>pictures/edit-pen.svg'></a>
    <a href='<?= base_url() ."profil/delete/$current_travel->id" ?>' class='delete'><img src='<?= base_url() ?>pictures/trash-bin-delete.svg'></a>
        
    <div class='selected-list'>
        <?php foreach ($current_keypoints as $keypoint) {
            echo "<div class='selected-item'><span> $keypoint->key_point_name </span></div>";
        } ?>
    </div>
        
    <!-- Carte -->
    <div id="map" style="height: 400px; width: 90%;"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let current_keypoints = <?= $current_keypoints ?>;
        let map, markers = [], route = null;

        function initMap() {
            map = L.map('map').setView([48.8566, 2.3522], 4);
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
                            .bindPopup(`<b>${current_kp['key_point_name']}</b>`)
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

        // Initialisation de la carte dans l'interface de voyage
        initMap();
        setRoute();
    });
</script>