<div class="content_box">
            <h3> Liste des villes actuelles : </h3>
            
            <span class="items_box">
                <?php 
                    if (count($cities) !== 0) {
                        foreach ($cities as $city): ?>
                            <?php 
                                echo "<span class='card'> 
                                        <span class='kp_btns_box'> 
                                            <a href='" . base_url() . "modifier_une_ville/$city->id' class='modify'> 
                                                <img src='" . base_url() . "pictures/edit-pen.svg'> 
                                            </a>
        
                                            <a href='" . base_url() . "liste_des_villes/delete/$city->id' class='delete'> 
                                                <img src='" . base_url() . "pictures/trash-bin-delete.svg'> 
                                            </a>
                                        </span>
        
                                        <p class='item'> <b> ID </b> : <i>" . "$city->id" . "</i> </p> 
                                        <p class='item'> <b> Nom </b> : <i> " . "$city->city_name" . "</i> </p>  
                                        <p class='item'> <b> Pays </b> : <i> " . "$city->city_country" . "</i> </p>
                                    </span>  \n";
                            ?>
                        <?php endforeach; 
                        
                    } else {
                        echo "<h4> Aucune ville n'est actuellement créée </h4>";  
                    } ?>
            </span>
        </div>
    </body>
</html>

