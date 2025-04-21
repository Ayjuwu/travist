<?php
    namespace App\Controllers;
    use App\Models\Tag;

    class TagsAjoutController extends BaseController {
        function index() {
            helper(['form']);
            $data['title']= "Créer un nouveau tag - Travist";

            echo view('includes/header_view', $data);
            echo view('ajouter_tag');
            echo view('includes/footer');
        }

        public function createTag() {
            $rule = [
                'tag_name' => 'required|min_length[2]|max_length[20]|is_unique[tags.tag_name]|regex_match[/^#?[\p{L}\p{N}]+$/u]',
            ];

            if ($this->validate($rule)) {
                $tag = new Tag();

                $rawName = $this->request->getVar('tag_name');
                $tagNameClean = ltrim($rawName, '#'); // enlève le # s’il y est

                // Vérifie si le tag existe en base avec ou sans #
                $tagExists = \App\Models\Tag::where('tag_name', $tagNameClean)
                    ->orWhere('tag_name', '#' . $tagNameClean)
                    ->first();

                if ($tagExists) {
                    return redirect()->to(base_url() . 'creer_un_tag')->with('error', 'Ce tag existe déjà avec ou sans #.');
                }

                // Ajoute # seulement s'il n'est pas déjà là
                $tagName = (str_starts_with($rawName, '#')) ? $rawName : '#' . $rawName;

                $tag->tag_name = $tagName;
                $tag->save();

                return redirect()->to(base_url() . 'liste_des_tags');
            } else {
                $data['validation'] = $this->validator;
                $this->index(); // Attention : tu ne passes pas $data ici, ce qui peut poser souci
            }
        }
    }