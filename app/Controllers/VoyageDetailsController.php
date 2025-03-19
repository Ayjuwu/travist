<?php
    namespace App\Controllers;
    use App\Models\Travel;
    use App\Models\Keypoint;
    
    class VoyageDetailsController extends BaseController {
        function index($id) {
            helper(['form']);
            $current_travel = Travel::find($id);

            $data['title'] = "Détails de votre voyage - Travist";
            $data['current_travel'] = Travel::find($id);
            $data['keypoints'] = Keypoint::all();

            $data['current_keypoints'] = $current_travel->keypoints()->get(); 
    
            echo view('includes/header_view', $data);
            echo view('details_voyage');
            echo view('includes/footer');
        }
    }