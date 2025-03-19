<?php
    namespace App\Controllers;
    use App\Models\Keypoint;
    use App\Models\Tag;

    class lieuxController extends BaseController {
        public function index() {
            $data['title']= "Liste des lieux - Travist";
            $data['keypoints'] = Keypoint::all();
            $data['tags'] = Tag::all();

            echo view('includes/header_view', $data);
            echo view('liste_lieux');
            echo view('includes/footer');
        }

        public function delete(int $id) {
            $keypoint = Keypoint::find($id);

            $keypoint->travels()->detach();
            $keypoint->tags()->detach();
            $keypoint->delete();

            return redirect()->to(base_url() . 'liste_des_lieux');
        }
    }