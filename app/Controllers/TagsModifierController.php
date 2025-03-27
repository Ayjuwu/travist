<?php
    namespace App\Controllers;
    use App\Models\Tag;

    class TagsModifierController extends BaseController {
        public function index(int $id) {
            helper(['form']);

            $data['title'] = "Modifier un point clé - Travist";
            $data['current_tag'] = Tag::find($id);

            if(!is_null($data['current_tag'])) {
                echo view('includes/header_view', $data);
                echo view('modifier_tag');
                echo view('includes/footer');
            } else {
                return redirect()->to(base_url() . 'liste_des_tags');
            }
        }

        public function modify(int $id) {
            if(!is_null($id)) {
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
            } else {
                return redirect()->to(base_url() . 'liste_des_tags');
            }
        }
    }