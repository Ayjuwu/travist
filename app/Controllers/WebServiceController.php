<?php
    namespace App\Controllers;
    use CodeIgniter\API\ResponseTrait;

    use App\Models\UserModel;
    use CodeIgniter\RESTful\ResourceController;
    use Exception;
    use \Firebase\JWT\JWT;
    use \Firebase\JWT\Key;
    use CodeIgniter\HTTP\RequestInterface;
    use CodeIgniter\HTTP\ResponseInterface;
    use Psr\Log\LoggerInterface;

    use App\Models\Travel;
    use App\Models\Keypoint;
    use App\Models\Tag;
    use App\Models\User;


    class WebServiceController extends BaseController {
        use ResponseTrait;
        
        public function getKeypoints() {
            $keypoints = Keypoint::all();
            return $this->respond($keypoints);
        }

        public function getTravels() {
            $travels = Travel::all();
            return $this->respond($travels);
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

        public function getKeypointsByTag(int $tag_id) {
            $tag = Tag::find($tag_id);

            if(!is_null($tag)) {
                $keypoints = $tag->keypoints()->get();
                return $this->respond($keypoints);
            }
        }

        public function getKeypointsByCountry(string $country) {
            $keypoints = Keypoint::join('cities', 'keypoints.city_id', '=', 'cities.id')
                ->where('cities.city_country', '=', $country)
                ->select('keypoints.*') // Pour récupérer uniquement les colonnes de keypoints
                ->get();

            return $this->respond($keypoints);
        }

        public function getKeypointsByCity(int $city_id) {
            $keypoints = Keypoint::where('city_id', '=', $city_id)->get();
            return $this->respond($keypoints);
        }

        public function getNearestKeypointPosition(float $latitude, float $longitude) {
            $keypoint = Keypoint::selectRaw(
                "*, ((key_point_gps_x - ?) * (key_point_gps_x - ?) + (key_point_gps_y - ?) * (key_point_gps_y - ?)) as distance",
                [$latitude, $latitude, $longitude, $longitude]
            )
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