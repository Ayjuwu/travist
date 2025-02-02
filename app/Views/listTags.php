<div class="content_box">
            <h3> Liste des tags actuels : </h3>
            
            <span class="items_box">
                <?php 
                    if (count($tags) !== 0) {
                        foreach ($tags as $tag): ?>
                            <?php 
                                echo "<span class='card'> 
                                        <span class='kp_btns_box'> 
                                            <a href='" . base_url() . "modifier_un_tag/$tag->id' class='modify'> 
                                                <img src='" . base_url() . "/pictures/edit-pen.svg'> 
                                            </a>
        
                                            <a href='" . base_url() . "liste_des_tags/delete/$tag->id' class='delete'> 
                                                <img src='" . base_url() . "/pictures/trash-bin-delete.svg'> 
                                            </a>
                                        </span>
        
                                        <p class='item'> <b> ID </b> : <i>" . "$tag->id" . "</i> </p> 
                                        <p class='item'> <b> Nom </b> : <i> " . "$tag->tag_name" . "</i> </p>  
                                    </span>  \n";
                            ?>
                        <?php endforeach; 
                        
                    } else {
                        echo "<h4> Aucun tag n'est actuellement créé </h4>";  
                    } ?>
            </span>
        </div>
    </body>
</html>

