<?php
    namespace App\Controllers;
    use App\Models\Tag;

    class TagModifyController extends BaseController {
        public function index(int $id) {
            helper(['form']);

            $data['title'] = "Modifier un point clé - Travist";
            $data['current_tag'] = Tag::find($id);

            if($data['current_tag'] === null) {
                return redirect()->to(base_url() . 'liste_des_tags');
            } else {
                echo view('includes/header_view', $data);
                echo view('modifyTag');
                echo view('includes/footer');
            }
        }

        public function modify(int $id) {

            if($id === null) {
                return redirect()->to(base_url() . 'liste_des_tags');
            } else {
                helper(['form']);
                $tag = Tag::find($id);
    
                $rule = [
                    'tag_name' => 'required|min_length[2]|max_length[20]|is_unique[tags.tag_name]|alpha',
                ];
    
                if ($this->validate($rule)) {
                    $tag->tag_name = '#' . $this->request->getVar('tag_name');
                    $tag->save();
    
                    return redirect()->to(base_url() . 'liste_des_tags');
                } else {
                    $data['validation'] = $this->validator;
                    $this->index($tag->id);
                }
            }
        }
    }