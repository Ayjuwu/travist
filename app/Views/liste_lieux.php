        <div class="content_box">
            <h3> Liste des lieux de visite actuels : </h3>
            
            <span class="items_box">
                <?php 
                    if (count($keypoints) !== 0) {
                        foreach ($keypoints as $keypoint): 
                            $keypoint_tags = $keypoint->tags()->get();
                            $keypoint_city = $keypoint->city()->first();
                            
                            echo "<span class='card' id='card'> 
                                    <span class='kp_btns_box'> 
                                        <a href='" . base_url() . "modifier_un_lieu/$keypoint->id' class='modify'> 
                                            <img src='" . base_url() . "pictures/edit-pen.svg'> 
                                        </a>
    
                                        <a href='" . base_url() . "liste_des_lieux/delete/$keypoint->id' class='delete'> 
                                            <img src='" . base_url() . "pictures/trash-bin-delete.svg'> 
                                        </a>
                                    </span>
    
                                    <p class='item'> <b> ID </b> : <i> $keypoint->id </i> </p> 
                                    <p class='item'> <b> Nom </b> : <i>" . esc($keypoint->key_point_name) . "</i> </p>  
                                    <p class='item'> <b> Prix </b> : <i>" . esc($keypoint->key_point_price) . "€ </i> </p> 
                                    <p class='item'> <b> Date de début </b> : <i>" . esc($keypoint->key_point_start_date) . "</i> </p> 
                                    <p class='item'> <b> Date de fin </b> : <i>" . esc($keypoint->key_point_end_date) . "</i> </p> 
                                    <p class='item'> <b> Ville la plus proche </b> : <i>" . esc($keypoint_city->city_name) . "</i></p>
                                    <p class='item'> <b> Coordonnée GPS X </b> : <i>" . esc($keypoint->key_point_gps_x) . "</i> </p>
                                    <p class='item'> <b> Coordonnée GPS Y </b> : <i>" . esc($keypoint->key_point_gps_y) . "</i> </p>
                                    <img src='data:image/png;base64, $keypoint->key_point_cover'/>
                                    <p class='item'> <b> Tag(s) </b> : "; 

                            foreach ($keypoint_tags as $keypoint_tag) {
                                foreach ($tags as $tag) {
                                    $matching_tag = $tag::find($keypoint_tag->pivot->tag_id);
                                } 
                                echo "<i>" . esc($matching_tag->tag_name) . " " . "</i>";
                            }

                            if ($keypoint_tags->isEmpty()) {
                                echo "<i>" . "#NaN" . "</i>";
                            }

                            echo "</p> </span> \n";
                        endforeach; 
                    } else {
                        echo "<h4> Aucun lieu de visite n'est actuellement créé </h4>";  
                    } ?>
            </span>
        </div>
    </body>
</html>