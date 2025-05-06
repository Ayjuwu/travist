<?php
    namespace App\Controllers;
    use App\Models\Keypoint;
    use App\Models\City;
    use App\Models\Tag;
   
    class lieuxAjoutController extends BaseController {
        public function index() {
            helper(['form']);

            $data['title'] = "Créer un nouveau lieu - Travist";
            $data['cities'] = City::all();
            $data['tags'] = Tag::all();

            echo view('includes/header_view', $data);
            echo view('ajouter_lieux');
            echo view('includes/footer');
        }

        public function createKeyPoint() {
            $rules = [
                'key_point_name' => 'required|min_length[3]|max_length[40]|is_unique[keypoints.key_point_name]|regex_match[/^[\p{L}\d\s\'\-]+$/u]',
                'key_point_price' => 'required|decimal',
                'key_point_start_date' => 'required|min_length[10]|max_length[10]|valid_date',
                'key_point_end_date' => 'required|min_length[10]|max_length[10]|valid_date',
                'key_point_cover' => 'uploaded[key_point_cover]|is_image[key_point_cover]|mime_in[key_point_cover,image/jpeg,image/jpg,image/png,image/webp]',
                'city' => 'required|integer',
                'key_point_gps_x' => 'required|max_length[50]|is_unique[keypoints.key_point_gps_x]|decimal',
                'key_point_gps_y' => 'required|max_length[50]|is_unique[keypoints.key_point_gps_y]|decimal',
            ];
        
            if ($this->validate($rules)) {
                $keypoint = new Keypoint();
        
                $keypoint->key_point_name = $this->request->getVar('key_point_name');
                $keypoint->key_point_price = $this->request->getVar('key_point_price');
                $keypoint->key_point_start_date = $this->request->getVar('key_point_start_date');
                $keypoint->key_point_end_date = $this->request->getVar('key_point_end_date');
                $keypoint->key_point_gps_x = $this->request->getVar('key_point_gps_x');
                $keypoint->key_point_gps_y = $this->request->getVar('key_point_gps_y');
        
                // Gestion de l'image
                $image = $this->request->getFile('key_point_cover');
                if ($image->isValid() && !$image->hasMoved()) {
                    // Récupérer le fichier temporaire
                    $imagePath = $image->getTempName();
                    $extension = $image->getExtension();

                    // Vérifier que l'image est de type JPG, JPEG, PNG ou WEBP
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                        // Obtenir les dimensions de l'image
                        list($width, $height) = getimagesize($imagePath);

                        // Choisir une nouvelle largeur et hauteur (par exemple 50% de la taille d'origine)
                        $newWidth = $width / 2;
                        $newHeight = $height / 2;

                        // Créer une nouvelle image redimensionnée
                        $imageResized = imagecreatetruecolor($newWidth, $newHeight);

                        // Créer une image source selon le type
                        if ($extension == 'jpg' || $extension == 'jpeg') {
                            $sourceImage = imagecreatefromjpeg($imagePath);
                        } elseif ($extension == 'png') {
                            $sourceImage = imagecreatefrompng($imagePath);
                        } elseif ($extension == 'webp') {
                            $sourceImage = imagecreatefromwebp($imagePath);
                        }

                        // Copier et redimensionner l'image dans l'image cible
                        imagecopyresampled($imageResized, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                        // Sauvegarder l'image redimensionnée en mémoire
                        ob_start();
                        if ($extension == 'jpg' || $extension == 'jpeg') {
                            imagejpeg($imageResized, null, 50);  // Compression JPG à 75%
                        } elseif ($extension == 'png') {
                            imagepng($imageResized, null, 6);    // Compression PNG à 6 (niveau de compression)
                        } elseif ($extension == 'webp') {
                            imagewebp($imageResized, null, 50);  // Compression WebP à 75% (comme JPEG)
                        }

                        // Récupérer les données de l'image redimensionnée en mémoire
                        $compressedData = ob_get_clean();

                        // Convertir l'image redimensionnée en base64
                        $base64Image = base64_encode($compressedData);

                        // Sauvegarder l'image en base64 dans la base de données
                        $keypoint->key_point_cover = $base64Image;
                        $keypoint->save();

                        // Libérer la mémoire
                        imagedestroy($imageResized);
                        imagedestroy($sourceImage);
                    }
                }
        
                // Récupération de l'ID de la ville
                $keypoint->city_id = $this->request->getVar('city'); 
        
                // Gestion de la checkbox (si non cochée, retourne 0)
                $keypoint->is_altered_keypoint = $this->request->getVar('is_altered_keypoint') ? 1 : 0;
        
                $keypoint->save();
        
                // Assigner les tags
                $tags = $this->request->getVar('tags');
                if (!empty($tags)) {
                    foreach ($tags as $id) {
                        $keypoint->tags()->attach($id);
                    }
                }
        
                return redirect()->to(base_url() . 'liste_des_lieux');
            } else {
                $data['validation'] = $this->validator;
                return $this->index();
            }
        }           
    }