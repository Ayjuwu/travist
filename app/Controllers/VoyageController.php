<?php
namespace App\Controllers;

use App\Models\Travel;
use App\Models\Keypoint;
use DateTime;

class VoyageController extends BaseController {
    public function index() {
        helper(['form']);

        $data['title'] = "Planifier un nouveau voyage - Travist";
        $data['keypoints'] = Keypoint::all();
        $data['userID'] = session()->get('user_id');

        // Récupérer les erreurs depuis la session
        $data['errors'] = session()->getFlashdata('errors');

        echo view('includes/header_view', $data);
        echo view('planifier_voyage', $data);
        echo view('includes/footer');
    }

    public function createTravel() {
        $rules = [
            'travel_name'    => 'required|min_length[2]|max_length[25]|regex_match[/^[\p{L}\s]+$/u]',
            'people_number'  => 'required|max_length[2]|numeric',
            'keypoints'      => 'required|is_array',
            'start_date'     => 'required|is_array',
            'end_date'       => 'required|is_array',
            'userID'         => 'required'
        ];

        if ($this->validate($rules)) {
            $userID = $this->request->getVar('userID');
            $keypoints = $this->request->getVar('keypoints'); // Tableau d'IDs
            $visitStartDates = $this->request->getVar('start_date'); // Tableau des dates de début
            $visitEndDates = $this->request->getVar('end_date'); // Tableau des dates de fin
            $errors = []; // Tableau pour stocker les messages d'erreur

            // Création du voyage
            $travel = new Travel();
            $travel->travel_name = $this->request->getVar('travel_name');
            $travel->people_number = $this->request->getVar('people_number');
            $travel->user_id = $userID;

            // Essayer de sauvegarder le voyage
            if (!$travel->save()) {
                return $this->index(); // Si la sauvegarde échoue, retour à la page
            }

            // Initialisation des variables pour les dates du voyage global
            $minStartDate = null;
            $maxEndDate = null;
            $individualPrice = 0;
            $totalPrice = 0;
            $visitedIntervals = [];

            // Parcourir chaque lieu
            foreach ($keypoints as $keypointId) {
                $keypoint = Keypoint::find((int)$keypointId);

                if ($keypoint && isset($visitStartDates[$keypointId]) && isset($visitEndDates[$keypointId])) {
                    $startRaw = $visitStartDates[$keypointId];
                    $endRaw = $visitEndDates[$keypointId];

                    if (empty($startRaw) || empty($endRaw)) {
                        $errors[] = "Les dates du lieu " . $keypoint->key_point_name . " sont manquantes."; // Erreur pour dates manquantes
                        $this->deleteTravel($travel->id);
                        session()->setFlashdata('errors', $errors); // Stocker les erreurs dans la session
                        return $this->index(); // Retour à la page
                    }

                    $startDate = new DateTime($startRaw);
                    $endDate = new DateTime($endRaw);

                    $keypointStartDate = new DateTime($keypoint->key_point_start_date);
                    $keypointEndDate = new DateTime($keypoint->key_point_end_date);

                    // 1. Vérification pour chaque lieu par rapport à la période de disponibilité du keypoint
                    if ($startDate < $keypointStartDate || $endDate > $keypointEndDate) {
                        $errors[] = "Les dates du lieu " . $keypoint->key_point_name . " sont en dehors de la période disponible.";
                        $this->deleteTravel($travel->id);
                        session()->setFlashdata('errors', $errors); // Stocker les erreurs dans la session
                        return $this->index(); // Retour à la page
                    }

                    // 2. Vérifier que la date de fin ne soit pas inférieure à la date de début et inversement
                    if ($endDate < $startDate || $startDate > $endDate) {
                        $errors[] = "La date de fin pour le lieu " . $keypoint->key_point_name . " est avant la date de début.";
                        $this->deleteTravel($travel->id);
                        session()->setFlashdata('errors', $errors); // Stocker les erreurs dans la session
                        return $this->index(); // Retour à la page
                    }

                    // 3. Vérification des chevauchements avec les autres points clés déjà sélectionnés
                    foreach ($visitedIntervals as $interval) {
                        if (($startDate >= $interval['start'] && $startDate <= $interval['end']) || 
                            ($endDate >= $interval['start'] && $endDate <= $interval['end']) || 
                            ($startDate <= $interval['start'] && $endDate >= $interval['end'])) {
                            $errors[] = "Les dates du lieu " . $keypoint->key_point_name . " se chevauchent avec un autre lieu.";
                            $this->deleteTravel($travel->id);
                            session()->setFlashdata('errors', $errors); // Stocker les erreurs dans la session
                            return $this->index(); // Retour à la page
                        }
                    }

                    // Ajouter l'intervalle du point clé actuel aux intervalles visités
                    $visitedIntervals[] = ['start' => $startDate, 'end' => $endDate];

                    // Déterminer les dates minimales et maximales globales du voyage
                    if (is_null($minStartDate) || $startDate < $minStartDate) {
                        $minStartDate = $startDate;
                    }
                    if (is_null($maxEndDate) || $endDate > $maxEndDate) {
                        $maxEndDate = $endDate;
                    }

                    // Associer le keypoint au voyage avec les dates
                    $travel->keypoints()->attach($keypoint->id, [
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date'   => $endDate->format('Y-m-d')
                    ]);

                    // Calculer le prix individuel
                    $individualPrice += $keypoint->key_point_price;

                    // Calculer le prix total
                    $totalPrice += $keypoint->key_point_price * $travel->people_number;
                }
            }

            // Vérification des dates globales du voyage
            if ($minStartDate === null || $maxEndDate === null) {
                $errors[] = "Les dates globales du voyage sont invalides.";
                $this->deleteTravel($travel->id); // Supprimer le voyage si les dates sont invalides
                session()->setFlashdata('errors', $errors); // Stocker les erreurs dans la session
                return $this->index(); // Retour à la page
            }

            // Mise à jour du voyage avec les dates finales et le prix total
            $travel->update([
                'travel_start_date' => $minStartDate->format('Y-m-d'),
                'travel_end_date'   => $maxEndDate->format('Y-m-d'),
                'individual_price'  => $individualPrice,
                'total_price'       => $totalPrice
            ]);

            return redirect()->to(base_url() . 'profil');
        } else {
            return $this->index(); // Si la validation échoue, retour à la page
        }
    }

    // Fonction pour supprimer un voyage en cas d'erreur
    private function deleteTravel($travelId) {
        $travel = Travel::find($travelId);
        if ($travel) {
            $travel->keypoints()->detach();
            $travel->delete();
        }
    }

    public function delete(int $id) {
        $travel = Travel::find($id);
        if ($travel) {
            $travel->keypoints()->detach();
            $travel->delete();
        }
        return redirect()->to(base_url() . 'profil');
    }
}
