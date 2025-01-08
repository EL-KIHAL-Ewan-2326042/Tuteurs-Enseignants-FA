<?php

namespace Blog\Models;

class Model {

    /**
     * Géocode une adresse
     * @param string $address
     * @return array|null Contient latitude et longitude
     */
    public function geocodeAddress(string $address): ?array
    {
        $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($address);

        $options = [
            "http" => [
                "header" => "User-Agent: MonApplication/1.0 (contact@monapplication.com)"
            ]
        ];

        $context = stream_context_create($options);

        try {
            $response = @file_get_contents($url, false, $context);
        }
        catch (\Exception $e) {
            return null;
        }

        $data = json_decode($response, true);

        if (!empty($data)) {
            return [
                'lat' => $data[0]['lat'],
                'lng' => $data[0]['lon']
            ];
        }

        return null;
    }

    /**
     * Calcule la durée entre un stage et un professeur avec OSRM
     * @param array $latLngInternship Latitude et longitude de l'origine
     * @param array $latLngTeacher Latitude et longitude de la destination
     * @return float|int|null Durée en minutes, ou null en cas d'erreur
     */
    public function calculateDuration(array $latLngInternship, array $latLngTeacher): float|int|null
    {
        $url = "http://router.project-osrm.org/route/v1/driving/{$latLngInternship['lng']},{$latLngInternship['lat']};{$latLngTeacher['lng']},{$latLngTeacher['lat']}?overview=false&alternatives=false&steps=false";

        $options = [
            "http" => [
                "header" => "User-Agent: MonApplication/1.0 (contact@monapplication.com)"
            ]
        ];

        $context = stream_context_create($options);
        try {
            $response = @file_get_contents($url, false, $context);
        }
        catch (\Exception $e) {
            return 60;
        }

        $data = json_decode($response, true);

        if (isset($data['routes'][0]['duration'])) {
            $duration = round($data['routes'][0]['duration'] / 60);
        }
        else {
            return null;
        }

        if ($duration >= 9999999) {
            return 60;
        }
        else {
            return $duration;
        }

    }

    public function calculateRelevanceTeacherStudentsAssociate(array $teacher, array $dictCoef, array $internship): array{
        $identifier = $teacher['id_teacher'];
        $dictValues = array();

        // Calculer les valeurs uniquement si elles sont nécessaires
        if (isset($dictCoef['Distance'])) {
            $dictValues["Distance"] = $this->getDistance($internship['internship_identifier'], $identifier, isset($internship['id_teacher']));
        }

        if (isset($dictCoef['Cohérence'])) {
            $dictValues["Cohérence"] = round($this->scoreDiscipSubject($internship['internship_identifier'], $identifier), 2);
        }

        if (isset($dictCoef['A été responsable'])) {
            $internshipListData = $this->getInternships($internship['internship_identifier']);
            $dictValues["A été responsable"] = $internshipListData;
        }

        if (isset($dictCoef['Est demandé'])) {
            $dictValues["Est demandé"] = $this->isRequested($internship['internship_identifier'], $identifier);
        }

        $totalScore = 0;
        $totalCoef = 0;

        // Pour chaque critère dans le dictionnaire de coefficients, calculer le score associé
        foreach ($dictCoef as $criteria => $coef) {
            if (isset($dictValues[$criteria])) {
                $value = $dictValues[$criteria];

                switch ($criteria) {
                    case 'Distance':
                        $ScoreDuration = $coef / (1 + 0.02 * $value);
                        $totalScore += $ScoreDuration;
                        break;

                    case 'A été responsable':
                        $numberOfInternships = count($value);
                        $baselineScore = 0.7 * $coef;

                        if ($numberOfInternships > 0) {
                            $ScoreInternship = $coef * min(1, log(1 + $numberOfInternships, 2));
                        } else {
                            $ScoreInternship = $baselineScore;
                        }

                        $totalScore += $ScoreInternship;
                        break;

                    case 'Est demandé':
                    case 'Cohérence':
                        $ScoreRelevance = $value * $coef;
                        $totalScore += $ScoreRelevance;
                        break;

                    default:
                        $totalScore += $value * $coef;
                        break;
                }
                $totalCoef += $coef;
            }
        }

        // Score normalise sur 5
        $ScoreFinal = ($totalScore * 5) / $totalCoef;

        $newList = ["id_teacher" => $identifier, "teacher_name" => $teacher["teacher_name"], "teacher_firstname" => $teacher["teacher_firstname"], "student_number" => $internship["student_number"], "student_name" => $internship["student_name"], "student_firstname" => $internship["student_firstname"], "internship_identifier" => $internship['internship_identifier'], "internship_subject" => $internship["internship_subject"], "address" => $internship["address"], "company_name" => $internship["company_name"], "formation" => $internship["formation"], "class_group" => $internship["class_group"], "score" => round($ScoreFinal, 2), "type" => $internship['type']];

        if (!empty($newList)) {
            return $newList;
        }

        return [[]];
    }

    /**
     * Algorithme de calcul du score de pertinence d'un stage pour un enseignant
     * @param array $dictValues tableau contenant les données relatives à chaque critère pour calculer le score final
     * @return float score sur 5
     */
    public function calculateScore(array $dictValues): float {
        $dictCoef = $this->getCoef($_SESSION['identifier']);

        $totalScore = 0;
        $totalCoef = 0;
        foreach ($dictValues as $criteria => $value) {
            if (isset($dictCoef[$criteria])) {
                $coef = $dictCoef[$criteria];

                switch ($criteria) {
                    case 'Distance':
                        $scoreDuration = $coef / (1 + 0.02 * $value);
                        $totalScore += $scoreDuration;
                        break;

                    case 'A été responsable':
                        $numberOfInternships = $value;
                        $baselineScore = 0.7 * $coef;

                        if ($numberOfInternships > 0) {
                            $ScoreInternship = $coef * min(1, log(1 + $numberOfInternships, 2));
                        } else {
                            $ScoreInternship = $baselineScore;
                        }

                        $totalScore += $ScoreInternship;
                        break;


                    case 'Cohérence':
                        $scoreRelevance = $value * $coef;
                        $totalScore += $scoreRelevance;
                        break;

                    default:
                        $totalScore += $value * $coef;
                        break;
                }

                $totalCoef += $coef;
            }
        }

        return (($totalScore * 5) / $totalCoef);
    }
}