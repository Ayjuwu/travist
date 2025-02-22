<?php
    namespace App\Controllers;
    use App\Models\Travel;
    use App\Models\Keypoint;
    
    class VoyageController extends BaseController {
        function index() {
            helper(['form']);

            $data['title'] = "Planifier un nouveau voyage - Travist";
            $data['keypoints'] = Keypoint::all();
            $data['userID'] = session()->get('user_id');
    
            echo view('includes/header_view', $data);
            echo view('planifier_voyage');
            echo view('includes/footer');
        }
    
        public function createTravel() {
            $rules = [
                'travel_name' => 'required|min_length[2]|max_length[25]|is_unique[travel.travel_name]|alpha',
                'people_number' => 'required|max_length[2]|numeric',
                'keypoints' => 'required',
                'userID' => 'required'  
            ];
        
            if ($this->validate($rules)) {
                $userID = $this->request->getVar('userID');
                $selectedKeypoints = $this->request->getVar('keypoints');
        
                $travel = new Travel();
        
                $travel->travel_name = $this->request->getVar('travel_name');
                $travel->people_number = $this->request->getVar('people_number');
                $travel->total_price = 0;
                $travel->user_id = $userID; // Associer l'utilisateur au voyage
        
                $travel->save();
        
                foreach ($selectedKeypoints as $keypointId) {
                    $keypoint = Keypoint::find($keypointId);
                    if ($keypoint) {
                        $travel->keypoints()->attach($keypointId);
                        $travel->total_price += $keypoint->key_point_price * $travel->people_number;
                    }
                }
        
                $travel->save();
                return redirect()->to(base_url() . 'profil');
            } else {
                $data['validation'] = $this->validator;
                $this->index();
            }
        }

        public function delete(int $id) {
            $travel = Travel::find($id);

            $travel->keypoints()->detach();
            $travel->delete();

            return redirect()->to(base_url() . 'profil');
        }
    }