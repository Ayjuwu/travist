<?php
    namespace App\Controllers;
    use App\Models\Tag;

    class TagAddController extends BaseController {
        function index() {
            helper(['form']);
            $data['title']= "Créer un nouveau tag - Travist";

            echo view('includes/header_view', $data);
            echo view('addTag');
            echo view('includes/footer');
        }

        public function createTag() {
            helper(['form']);
            $rule = ['tag_name' => 'required|min_length[2]|max_length[20]|is_unique[tags.tag_name]|alpha',];

            if ($this->validate($rule)) {
                $tag = new Tag();

                $tag->tag_name = '#' . $this->request->getVar('tag_name');
                $tag->save();

                return redirect()->to(base_url() . 'liste_des_tags');
            } else {
                $data['validation'] = $this->validator;
                $this->index();
            }
        }
    }