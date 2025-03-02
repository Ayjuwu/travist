<?php
    namespace App\Controllers;
    use App\Models\Tag;

    class TagsController extends BaseController {
        function index() {
            $data['title'] = "Liste des tags - Travist";
            $data['tags'] = Tag::all();

            echo view('includes/header_view', $data);
            echo view('listTags');
            echo view('includes/footer');
        }

        function delete(int $id) {
            $tag = Tag::find($id);

            $tag->keypoints()->detach();
            $tag->delete();

            return redirect()->to(base_url() . 'liste_des_tags');
        }
    }
    