        <div class="content_box">
            <h2> Bienvenue <?php echo esc($username); ?></h2>   
            
            <?php if (count($travels) !== 0) {
                foreach ($travels as $travel) {
                    echo "<a href='" . base_url() . "'#' class='card' id='list_card'>
                            <h4>" . $travel->travel_name . "</h4>

                            <span class='kp_btns_box'> 
                                <a href='" . base_url() . "modifier_un_voyage/$travel->id' class='modify'> 
                                    <img src='" . base_url() . "/pictures/edit-pen.svg'> 
                                </a>
                                <a href='" . base_url() . "profil/delete/$travel->id' class='delete'> 
                                    <img src='" . base_url() . "/pictures/trash-bin-delete.svg'> 
                                </a>
                            </span>
                        </a>";
                }
            } else {
                echo "<img src='" . base_url() . "pictures/no_travel_icon.png' alt='no travel found'>
                <p class='no_travel_text'> Vous n'avez aucun voyage programmé. </p>";
            } ?>
            
            <a href='<?= base_url() . 'planifier_un_voyage'?>' class='addTravelBtn'> Planifier un nouveau voyage </a>

        </div>
    </body>
</html>
