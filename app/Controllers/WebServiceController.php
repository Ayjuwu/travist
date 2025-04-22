<?php
    namespace App\Controllers;
    use CodeIgniter\API\ResponseTrait;
    use Illuminate\Support\Facades\DB;

    use CodeIgniter\RESTful\ResourceController;
    use Exception;
    use \Firebase\JWT\JWT;
    use \Firebase\JWT\Key;
    use CodeIgniter\HTTP\RequestInterface;
    use CodeIgniter\HTTP\ResponseInterface;
    use Psr\Log\LoggerInterface;

    use App\Models\Travel;
    use App\Models\Keypoint;
    use App\Models\City;
    use App\Models\Tag;
    use App\Models\User;


    class WebServiceController extends BaseController {
        use ResponseTrait;
        
        public function getKeypoints() {
            $keypoints = Keypoint::all();
            return $this->respond($keypoints);
        }

        public function getCities() {
            $cities = City::all();
            return $this->respond($cities);
        }

        public function getTags() {
            $tags = Tag::all();
            return $this->respond($tags);
        }

        public function getTagsByKeypoint(int $id) {
            $keypoint = Keypoint::find($id);

            if(!is_null($keypoint)) {
                $tags = $keypoint->tags()->get();
                return $this->respond($tags);
            }
        }

        public function getCityByKeypoint(int $id) {
            $keypoint = Keypoint::find($id);

            if(!is_null($keypoint)) {
                $city = $keypoint->city()->get()->first();
                return $this->respond($city);
            }
        }

        public function getTravelsByUser(int $user_id) {
            $travels = Travel::where('user_id', '=', $user_id)->get();
            return $this->respond($travels);
        }

        public function getKeypointById(int $id) {
            $keypoint = Keypoint::find($id);

            if(!is_null($keypoint)) {
                return $this->respond($keypoint);
            }
        }

        public function getKeypointsByTravel(int $id) {
            $travel = Travel::find($id);

            if ($travel) {
                $keypoints = $travel->keypoints()
                    ->with('city')
                    ->get();

                return $this->respond($keypoints);
            }
        }

        public function getNearestKeypointPosition(float $latitude, float $longitude, int $travelId) {
            // Récupérer les ID des keypoints déjà assignés à ce voyage
            $assignedKeypointIds = \App\Models\Assigned::where('travel_id', $travelId)
                ->pluck('keypoint_id')
                ->toArray();

            $keypoint = Keypoint::selectRaw(
                    "*, ((key_point_gps_x - ?) * (key_point_gps_x - ?) + (key_point_gps_y - ?) * (key_point_gps_y - ?)) as distance",
                    [$latitude, $latitude, $longitude, $longitude]
                )
                ->where('is_altered_keypoint', 0)
                ->whereNotIn('id', $assignedKeypointIds) 
                ->orderBy("distance", "asc")
                ->first();
        
            if (!$keypoint) {
                return $this->failNotFound("Aucun keypoint trouvé");
            }
        
            return $this->respond($keypoint);
        }            

        public function register() {	
            $rules = [
                "user_name" => "required",
                "user_email" => "required|max_length[30]|is_unique[users.user_email]|valid_email",
                "user_password" => "required",
            ];

            $messages = [
                "user_name" => [
                    "required" => "Name is required"
                ],
                "user_email" => [
                    "required" => "Email required",
                    "valid_email" => "Email address is not in format"
                ],
                "user_password" => [
                    "required" => "password is required"
                ],
            ];

            if (!$this->validate($rules, $messages)) {

                $response = [
                    'status' => 500,
                    'error' => true,
                    'message' => $this->validator->getErrors(),
                    'data' => []
                ];
            } else {

                $user = new User();
                $user->user_name=$this->request->getVar("user_name");
                $user->user_email=$this->request->getVar("user_email");
                $user->user_password=password_hash($this->request->getVar("user_password"), PASSWORD_DEFAULT);
            
                if ($user->save()) {

                    $response = [
                        'status' => 200,
                        "error" => false,
                        'messages' => 'Successfully, user has been registered',
                        'data' => []
                    ];
                } else {

                    $response = [
                        'status' => 500,
                        "error" => true,
                        'messages' => 'Failed to create user',
                        'data' => []
                    ];
                }
            }
            $statcode=$response["status"];
            if ($statcode == 200){
                return $this->respond($response);    
            }else{
                return $this->fail($response,$statcode);
            }
            
        }

        private function getKey() {
            return ("41zeEZ1Fr98verz19A8v1ezrv984z8EF49");
        }

        public function login() {
            $rules = [
                "user_email" => "required|valid_email|min_length[6]",
                "user_password" => "required",
            ];

            $messages = [
                "user_email" => [
                    "required" => "Email required",
                    "valid_email" => "Email address is not in format"
                ],
                "user_password" => [
                    "required" => "password is required"
                ],
            ];

            if (!$this->validate($rules, $messages)) {

                $response = [
                    'status' => 400,
                    'error' => true,
                    'message' => $this->validator->getErrors(),
                    'data' => []
                ];

                return $this->fail($response);
                
            } else {
                

                $user = User::where("user_email", $this->request->getVar("user_email"))->first();

                if (!empty($user)) {

                    if (password_verify($this->request->getVar("user_password"), $user->user_password)) {

                        $key = $this->getKey();

                        $iat = time(); // current timestamp value
                        $nbf = $iat + 10;
                        $exp = $iat + 3600;

                        $payload = array(
                            "iss" => "The_claim",
                            "aud" => "The_Aud",
                            "iat" => $iat, // issued at
                            "nbf" => $nbf, //not before in seconds
                            "exp" => $exp, // expire time in seconds
                            "data" => $user,
                        );

                        $token = JWT::encode($payload, $key, "HS256");

                        $response = [
                            'status' => 200,
                            'error' => false,
                            'messages' => 'User logged In successfully',
                            'data' => [
                                'token' => $token
                            ]
                        ];
                        return $this->respond($response);
                    } else {

                        $response = [
                            'status' => 401,
                            'error' => true,
                            'messages' => 'Incorrect details',
                            'data' => []
                        ];
                        return $this->fail($response,401);
                    }
                } else {
                    $response = [
                        'status' => 401,
                        'error' => true,
                        'messages' => 'User not found',
                        'data' => []
                    ];
                    return $this->fail($response,401);
                }
            }
        }

        public function details() {
            
            try {
                $key = $this->getKey();
                $authHeader = $this->request->getHeader("Authorization");
                if(is_null($authHeader)){
                    throw(new Exception('null header'));
                }
                $authHeader = $authHeader->getValue();
                $token = $authHeader;
                JWT::$leeway = 60; // $leeway in seconds
                $decoded = JWT::decode($token, new Key($key, 'HS256'));

                if ($decoded) {

                    $response = [
                        'status' => 200,
                        'error' => false,
                        'messages' => 'User details',
                        'data' => [
                            'profile' => $decoded
                        ]
                    ];
                    return $this->respond($response);
                }

            } catch (Exception $ex) {
            
                $response = [
                    'status' => 401,
                    'error' => true,
                    'messages' => 'Access denied with dbg:'.$authHeader.$ex->getMessage(),
                    'data' => []
                ];
                return $this->fail($response,401);
            }
        }

        public function createTravel() {
            // Récupérer et décoder les données JSON envoyées via POST
            $data = $this->request->getJSON(true);
        
            // Vérifier que les champs obligatoires sont présents
            if (!isset($data['travel_name'], 
                       $data['people_number'], 
                       $data['user_id'], 
                       $data['travel_start_date'], 
                       $data['travel_end_date'], 
                       $data['individual_price'], 
                       $data['total_price'])) {
                
                return $this->failValidationError('Informations manquantes pour créer un voyage');
            }
            
            try {
                // Utilisation d'Eloquent pour créer l'enregistrement Travel
                // Mass assignment grâce à la propriété $fillable de votre modèle Travel
                $travel = Travel::create([
                    'travel_name'        => $data['travel_name'],
                    'people_number'      => $data['people_number'],
                    'user_id'            => $data['user_id'],
                    'travel_start_date'  => $data['travel_start_date'],
                    'travel_end_date'    => $data['travel_end_date'],
                    'individual_price'   => $data['individual_price'],
                    'total_price'        => $data['total_price']
                ]);
                
                // Si un tableau de keypoints est envoyé, on rattache chaque keypoint à ce voyage
                if (isset($data['keypoints']) && is_array($data['keypoints'])) {
                    foreach ($data['keypoints'] as $kp) {
                        // Chaque item doit contenir keypoint_id, start_date et end_date
                        if (isset($kp['keypoint_id'], $kp['start_date'], $kp['end_date'])) {
                            // Utilisation de la relation définie dans le modèle Travel (Eloquent)
                            // La méthode attach permet d'insérer dans la table pivot 'assigned'
                            $travel->keypoints()->attach($kp['keypoint_id'], [
                                'start_date' => $kp['start_date'],
                                'end_date'   => $kp['end_date']
                            ]);
                        }
                    }
                }
                
                // Préparer la réponse en cas de succès
                $response = [
                    'success' => true,
                    'status'  => 200,
                    'error'   => false,
                    'message' => 'Voyage créé avec succès',
                    'data'    => [
                        'travel' => $travel
                    ]
                ];
                
                return $this->respond($response, 200);
                
            } catch (\Exception $ex) {
                // En cas d'erreur, renvoyer une réponse avec le message d'erreur
                $response = [
                    'status'   => 500,
                    'error'    => true,
                    'message'  => 'Erreur lors de la création du voyage : ' . $ex->getMessage(),
                    'data'     => []
                ];
                return $this->fail($response, 500);
            }
        }

        public function insertAssigned() {
            // Récupérer et décoder les données JSON envoyées via POST
            $data = $this->request->getJSON(true);
            
            // Vérifier que les champs obligatoires sont présents
            if (!isset($data['travel_id'], $data['keypoints']) || !is_array($data['keypoints'])) {
                return $this->failValidationError('Informations manquantes pour l\'assignation des keypoints');
            }
        
            log_message('info', 'Données reçues pour insertAssigned: ' . print_r($data, true));
        
            // Vérifier si le voyage existe
            $travel = Travel::find($data['travel_id']);
            if (!$travel) {
                return $this->failNotFound('Voyage non trouvé');
            }
        
            // Boucler à travers les keypoints et les associer au voyage
            foreach ($data['keypoints'] as $kp) {
                if (isset($kp['keypoint_id'], $kp['start_date'], $kp['end_date'])) {
                    $travel->keypoints()->attach($kp['keypoint_id'], [
                        'start_date' => $kp['start_date'],
                        'end_date'   => $kp['end_date']
                    ]);
                } else {
                    return $this->failValidationError('Informations manquantes pour un keypoint');
                }
            }
        
            // Retourner une réponse de succès
            $response = [
                'success' => true,
                'status'  => 200,
                'message' => 'Keypoints assignés avec succès',
                'data'    => [
                    'travel_id' => $travel->id,
                    'assigned_keypoints' => $data['keypoints']
                ]
            ];
        
            return $this->respond($response, 200);
        }      
        
        public function deleteTravel(int $id) {
            $travel = Travel::find($id);
            
            if(!$travel) {
                return response()->setJSON(['success' => false, 'message' => 'Voyage non trouvé'], 404);
            }
            
            $travel->keypoints()->detach();
            $travel->delete();
        
            return $this->response->setJSON(['success' => true]);
        }       
        
        public function updateTravel(int $id) {
            $data = $this->request->getJSON(true);
        
            if (!isset($data['travel_id'], 
                       $data['travel_name'], 
                       $data['people_number'], 
                       $data['user_id'], 
                       $data['travel_start_date'], 
                       $data['travel_end_date'], 
                       $data['individual_price'], 
                       $data['total_price'])) {
                
                return $this->failValidationError('Informations manquantes pour la mise à jour du voyage');
            }
        
            try {
                $travel = Travel::find($id);
                if (!$travel) {
                    return $this->failNotFound('Voyage non trouvé');
                }
        
                // Mise à jour des champs
                $travel->update([
                    'travel_name'        => $data['travel_name'],
                    'people_number'      => $data['people_number'],
                    'user_id'            => $data['user_id'],
                    'travel_start_date'  => $data['travel_start_date'],
                    'travel_end_date'    => $data['travel_end_date'],
                    'individual_price'   => $data['individual_price'],
                    'total_price'        => $data['total_price']
                ]);
        
                // Mettre à jour les keypoints si fournis
                if (isset($data['keypoints']) && is_array($data['keypoints'])) {
                    // Détacher les anciens
                    $travel->keypoints()->detach();
        
                    // Réattacher les nouveaux
                    foreach ($data['keypoints'] as $kp) {
                        if (isset($kp['keypoint_id'], $kp['start_date'], $kp['end_date'])) {
                            $travel->keypoints()->attach($kp['keypoint_id'], [
                                'start_date' => $kp['start_date'],
                                'end_date'   => $kp['end_date']
                            ]);
                        }
                    }
                }
        
                return $this->respond([
                    'success' => true,
                    'status'  => 200,
                    'message' => 'Voyage mis à jour avec succès',
                    'data'    => $travel
                ]);
        
            } catch (\Exception $ex) {
                return $this->failServerError('Erreur lors de la mise à jour : ' . $ex->getMessage());
            }
        }

        public function updateAssigned(int $id) {
            $data = $this->request->getJSON(true);
        
            if (!isset($data['travel_id'], $data['keypoints']) || !is_array($data['keypoints'])) {
                return $this->failValidationError('Informations manquantes pour la mise à jour des keypoints assignés');
            }
        
            // Rechercher le voyage
            $travel = Travel::find($id);
            if (!$travel) {
                return $this->failNotFound('Voyage non trouvé');
            }
        
            try {
                // Supprimer toutes les assignations actuelles
                $travel->keypoints()->detach();
        
                // Réattacher les nouveaux keypoints
                foreach ($data['keypoints'] as $kp) {
                    if (isset($kp['keypoint_id'], $kp['start_date'], $kp['end_date'])) {
                        $travel->keypoints()->attach($kp['keypoint_id'], [
                            'start_date' => $kp['start_date'],
                            'end_date'   => $kp['end_date']
                        ]);
                    } else {
                        return $this->failValidationError('Données incomplètes pour un keypoint');
                    }
                }
        
                return $this->respond([
                    'success' => true,
                    'status'  => 200,
                    'message' => 'Keypoints mis à jour avec succès',
                    'data'    => [
                        'travel_id' => $travel->id,
                        'assigned_keypoints' => $data['keypoints']
                    ]
                ]);
            } catch (\Exception $ex) {
                return $this->failServerError('Erreur lors de la mise à jour des keypoints : ' . $ex->getMessage());
            }
        }   
        
        public function deleteAssigned(int $travel_id, int $keypoint_id) {
            $travel = Travel::find($travel_id);
            if (! $travel) {
                return $this->failNotFound('Voyage non trouvé');
            }

            $exists = $travel->keypoints()
                            ->where('keypoint_id', $keypoint_id)
                            ->exists();
            if (! $exists) {
                return $this->failNotFound('Aucun keypoint assigné trouvé pour cet ID');
            }

            try {
                $travel->keypoints()->detach($keypoint_id);
            } catch (\Exception $e) {
                return $this->failServerError('Erreur lors de la suppression du keypoint assigné : ' . $e->getMessage());
            }

            $assigned = $travel->keypoints()
                            ->withPivot('start_date', 'end_date')
                            ->get();

            if ($assigned->isEmpty()) {

                $travel->update([
                    'individual_price'   => 0,
                    'total_price'        => 0,
                    'travel_start_date'  => null,
                    'travel_end_date'    => null,
                ]);
            } else {
                $individualPrice = $assigned->sum('key_point_price');
                $totalPrice      = $individualPrice * $travel->people_number;

                $startDates = $assigned->pluck('pivot.start_date')->toArray();
                $endDates   = $assigned->pluck('pivot.end_date')->toArray();

                // trouver la date min et max
                $earliest = min($startDates);
                $latest   = max($endDates);

                $travel->update([
                    'individual_price'   => $individualPrice,
                    'total_price'        => $totalPrice,
                    'travel_start_date'  => $earliest,
                    'travel_end_date'    => $latest,
                ]);
            }

            return $this->respondDeleted([
                'success'    => true,
                'travel'     => $travel->fresh(), // recharge les données
            ]);
        }

        // Créer un tag
        public function createTag() {
            $data = $this->request->getJSON(true);
            try {
                $tag = Tag::create([
                    'tag_name' => $data['tag_name']
                ]);
                return $this->respondCreated([
                    'success' => true,
                    'data' => $tag
                ]);
            } catch (\Exception $e) {
                return $this->failServerError('Erreur création tag : ' . $e->getMessage());
            }
        }

        // Mettre à jour un tag existant
        public function updateTag(int $id) {
            $data = $this->request->getJSON(true);
            $tag = Tag::find($id);
            if (!$tag) {
                return $this->failNotFound('Tag non trouvé');
            }

            try {
                $tag->update(['tag_name' => $data['tag_name']]);
                return $this->respond([
                    'success' => true,
                    'data' => $tag
                ]);
            } catch (\Exception $e) {
                return $this->failServerError('Erreur mise à jour tag : ' . $e->getMessage());
            }
        }

        // Supprimer un tag
        public function deleteTag(int $id) {
            $tag = Tag::find($id);
            if (!$tag) {
                return $this->failNotFound('Tag non trouvé');
            }

            try {
                $tag->keypoints()->detach();
                $tag->delete();
                return $this->respondDeleted(['success' => true]);
            } catch (\Exception $e) {
                return $this->failServerError('Erreur suppression tag : ' . $e->getMessage());
            }
        }


        // Créer une ville
        public function createCity() {
            $data = $this->request->getJSON(true);
            try {
                $city = City::create([
                    'city_name' => $data['city_name'],
                    'city_country' => $data['city_country']
                ]);
                return $this->respondCreated([
                    'success' => true,
                    'data' => $city
                ]);
            } catch (\Exception $e) {
                return $this->failServerError('Erreur création ville : ' . $e->getMessage());
            }
        }

        // Mettre à jour une ville existante
        public function updateCity(int $id) {
            $data = $this->request->getJSON(true);
            $keypoints = Keypoint::where('city_id', '=', $id);

            $city = City::find($id);
            if (!$city) {
                return $this->failNotFound('Ville non trouvée');
            }

            try {
                foreach ($keypoints as $keypoint) {
                    $keypoint->update(['city_id' => $data['city_id']]);
                }

                $city->update(['city_name' => $data['city_name']]);
                $city->update(['city_country' => $data['city_country']]);
                return $this->respond([
                    'success' => true,
                    'data' => $city
                ]);
            } catch (\Exception $e) {
                return $this->failServerError('Erreur mise à jour ville : ' . $e->getMessage());
            }
        }

        // Supprimer une ville
        public function deleteCity(int $id) {
            $city = City::find($id);
            $keypoints = Keypoint::where('city_id', '=', $id)->get();

            if (!$city) {
                return $this->failNotFound('Ville non trouvée');
            }

            try {
                foreach ($keypoints as $keypoint) {
                   $keypoint->city_id = 0;
                   $keypoint->save();
                }

                // Puis supprimer la ville
                $city->delete();

                return $this->respondDeleted(['success' => true]);
            } catch (\Exception $e) {
                return $this->failServerError('Erreur suppression ville : ' . $e->getMessage());
            }
        }

        public function createKeypoint() {
            $data = $this->request->getJSON(true);

            try {
                // Création du keypoint
                $keypoint = Keypoint::create([
                    'key_point_name' => $data['key_point_name'],
                    'key_point_price' => $data['key_point_price'],
                    'key_point_start_date' => $data['key_point_start_date'],
                    'key_point_end_date' => $data['key_point_end_date'],
                    'key_point_cover' => $data['key_point_cover'],
                    'key_point_gps_x' => $data['key_point_gps_x'],
                    'key_point_gps_y' => $data['key_point_gps_y'],
                    'is_altered_keypoint' => $data['is_altered_keypoint'],
                    'city_id' => $data['city_id']
                ]);

                // Si on reçoit un tableau "tags", on rattache en pivot
                if (!empty($data['tags']) && is_array($data['tags'])) {
                    foreach ($data['tags'] as $tagId) {
                        if (Tag::find($tagId)) {
                            $keypoint->tags()->attach($tagId);
                        }
                    }
                }

                return $this->respondCreated([
                    'success' => true,
                    'data'    => $keypoint
                ]);

            } catch (\Exception $ex) {
                return $this->failServerError('Erreur création keypoint : ' . $ex->getMessage());
            }
        }

        public function updateKeypoint(int $id) {
            $data = $this->request->getJSON(true);

            $required = [
                'key_point_name',
                'key_point_price',
                'key_point_start_date',
                'key_point_end_date',
                'key_point_gps_x',
                'key_point_gps_y',
                'is_altered_keypoint',
                'city_id',
            ];
            foreach ($required as $f) {
                if (! array_key_exists($f, $data)) {
                    return $this->failValidationError("Champ manquant : {$f}");
                }
            }

            $kp = Keypoint::find($id);
            if (! $kp) {
                return $this->failNotFound('Keypoint non trouvé');
            }

            try {
                $upd = [
                    'key_point_name'       => $data['key_point_name'],
                    'key_point_price'      => $data['key_point_price'],
                    'key_point_start_date' => $data['key_point_start_date'],
                    'key_point_end_date'   => $data['key_point_end_date'],
                    'key_point_gps_x'      => $data['key_point_gps_x'],
                    'key_point_gps_y'      => $data['key_point_gps_y'],
                    'is_altered_keypoint'  => $data['is_altered_keypoint'],
                    'city_id'              => $data['city_id'],
                ];

                if (! empty($data['key_point_cover'])) {
                    $upd['key_point_cover'] = $data['key_point_cover'];
                }
                $kp->update($upd);

                // 4) Synchronisation des tags pivot
                if (isset($data['tags']) && is_array($data['tags'])) {
                    $kp->tags()->detach();
                    foreach ($data['tags'] as $tagId) {
                        if (Tag::find($tagId)) {
                            $kp->tags()->attach($tagId);
                        }
                    }
                }

                return $this->respond([
                    'success' => true,
                    'data'    => $kp
                ]);
            } catch (\Exception $ex) {
                return $this->failServerError('Erreur mise à jour keypoint : ' . $ex->getMessage());
            }
        }

        // Supprimer un tag
        public function deleteKeypoint(int $id) {
            $keypoint = Keypoint::find($id);

            try {
                if ($keypoint) {
                    $keypoint->travels()->detach();
                    $keypoint->tags()->detach();
                    $keypoint->delete();
                }

                return $this->respondDeleted(['success' => true]);
            } catch (\Exception $e) {
                return $this->failServerError('Erreur suppression ville : ' . $e->getMessage());
            }
        }


        public function failValidationError($message = "Validation error") {
            return $this->response->setStatusCode(400)->setJSON([
                'title' => 'Error',
                'type' => 'Validation Error',
                'message' => $message
            ]);
        }
        
        public function failNotFound($message = "Not Found") {
            return $this->response->setStatusCode(404)->setJSON([
                'title' => 'Error',
                'type' => 'Not Found',
                'message' => $message
            ]);
        }        
    }