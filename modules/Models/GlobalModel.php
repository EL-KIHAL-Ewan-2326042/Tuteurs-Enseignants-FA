<?php

namespace Blog\Models;

use Includes\Database;
use PDO;

class GlobalModel {
    private Database $db;
    public function __construct(Database $db){
        $this->db = $db;
    }

    /**
     * Renvoie tous les départements de l'enseignant passé en paramètre
     * @param string $teacher_id identifiant de l'enseignant
     * @return false|array tableau contenant tous les départements dont l'enseignant connecté fait partie, false sinon
     */
    public function getDepTeacher(string $teacher_id): false|array {
        $query = 'SELECT DISTINCT department_name
                    FROM has_role
                    WHERE user_id = :teacher_id';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie un tableau contenant les stages des élèves du département passé en paramètre et leurs informations à condition que les stages ne soient ni passés et qu'aucun tuteur ne leur soit attribué
     * @param string $department le département duquel les élèves sélectionnés font partie
     * @return false|array tableau contenant le numéro, le nom et le prénom de l'élève, ainsi que le nom de l'entreprise dans lequel il va faire son stage, le sujet et le numéro du stage, false sinon
     */
    public function getInternshipsPerDepartment(string $department): false|array {
        $query = 'SELECT internship_identifier, company_name, internship_subject, address, internship.student_number, id_teacher, student_name, student_firstname, type, formation, class_group
                    FROM internship
                    JOIN student ON internship.student_number = student.student_number
                    JOIN study_at ON internship.student_number = study_at.student_number
                    WHERE department_name = :department_name
                    AND id_teacher IS NULL
                    AND start_date_internship > CURRENT_DATE';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':department_name', $department);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie un tableau contenant les informations de chaque tutorat terminé de l'élève passé en paramètre
     * @param string $student le numéro de l'étudiant dont on récupère les informations
     * @return false|array tableau contenant, pour chaque tutorat, le numéro d'enseignant du tuteur, le numéro de l'élève et les dates, false sinon
     */
    public function getInternships(string $student): false|array {
        $query = 'SELECT id_teacher, student_number, Start_date_internship, End_date_internship
                    FROM internship
                    WHERE student_number = :student
                    AND end_date_internship < CURRENT_DATE
                    AND id_teacher IS NOT NULL
                    ORDER BY start_date_internship ASC';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':student', $student);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie un score associé à la pertinence entre le sujet du stage et les disciplines enseignées par le professeur, tous deux passés en paramètre
     * @param string $internshipId numéro du stage
     * @param string $identifier identifiant de l'enseignant
     * @return float score associé à la pertinence entre le sujet de stage et les disciplines enseignées par le professeur connecté
     */
    public function scoreDiscipSubject(string $internshipId, string $identifier): float {
        $pdo = $this->db;

        // on récupère les mots-clés relatifs au sujet du stage
        $query = "SELECT keywords
                    FROM internship
                    WHERE internship_identifier = :internshipId";
        $stmt1 = $pdo->getConn()->prepare($query);
        $stmt1->bindParam(':internshipId', $internshipId);
        $stmt1->execute();
        $result = $stmt1->fetch(PDO::FETCH_ASSOC);

        // si on n'a trouvé aucun mot-clé, alors on renvoie 0
        if (!$result) return 0;
        $searchTerm1 = $result['keywords'];

        $searchTerm1 = trim($searchTerm1);
        $tsQuery1 = implode(' | ', explode(' ', $searchTerm1));
        $tsQuery1 = implode(' & ', explode('_', $tsQuery1));

        // on récupère les disciplines enseignées par l'enseignant
        $query = "SELECT discipline_name FROM is_taught WHERE id_teacher = :id";
        $stmt2 = $pdo->getConn()->prepare($query);
        $stmt2->bindParam(':id', $identifier);
        $stmt2->execute();
        $result = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // si on n'a trouvé aucune discipline, alors on renvoie 0
        if (!$result) return 0;
        $searchTerm2 = "";

        for($i = 0; $i < count($result); ++$i) {
            $searchTerm2 .= $result[$i]['discipline_name'];
            if($i < count($result) - 1) $searchTerm2 .= ' ';
        }

        $searchTerm2 = trim($searchTerm2);
        $tsQuery2 = implode(' | ', explode(' ', $searchTerm2));
        $tsQuery2 = implode(' & ', explode('_', $tsQuery2));

        // on convertit les mots-clés et les disciplines pour pouvoir les comparer
        $query = "SELECT to_tsquery('french', :searchTerm1) AS internship, to_tsquery('french', :searchTerm2) AS discip";
        $stmt3 = $pdo->getConn()->prepare($query);
        $stmt3->BindValue(':searchTerm1', $tsQuery1);
        $stmt3->bindValue(':searchTerm2', $tsQuery2);
        $stmt3->execute();

        $result = $stmt3->fetch(PDO::FETCH_ASSOC);

        $internship = explode(' | ', $result['internship']);
        $disciplines = explode(' | ', $result['discip']);

        $score = 0;

        foreach ($internship as $subject) {
            $subj = explode(' & ', $subject);
            foreach ($disciplines as $discipline) {
                // si un mot-clé et une discipline sont égaux, alors on rajoute 1/[nombre de mots-clés]
                if ($subject == $discipline) $score += 1/count($internship);
                else {  // sinon si un mot dans le mot-clé correspond à un mot dans la discipline, alors on rajoute 1/([nombre de mots-clés] * [nombre de mot dans le mot-clé])
                    foreach ($subj as $sub) {
                        foreach (explode(' & ', $discipline) as $discip) {
                            if ($discip == $sub) $score += 1/(count($internship)*count($subj));
                        }
                    }
                    // si le score est égal à 1, alors le maximum a été atteint, aucun point ne sera rajouté au score donc on sort de la boucle
                } if ($score === 1) break;
            } if ($score === 1) break;
        }

        return $score;
    }

    public function isRequested(string $internship_identifier, string $id_teacher): bool {

        $query = "SELECT * FROM is_requested WHERE internship_identifier = :internship_identifier AND id_teacher = :id_teacher";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':internship_identifier', $internship_identifier);
        $stmt->bindParam(':id_teacher', $id_teacher);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) return 0;
        return 1;
    }

    /**
     * Calcul de distance entre un eleve et un professeur
     * @param string $internship_identifier l'identifiant du stage
     * @param string $id_teacher l'identifiant du professeur
     * @param bool $bound true si un enseignant est déjà associé au stage, false sinon
     * @return int distance en minute entre les deux
     */
    public function getDistance(string $internship_identifier, string $id_teacher, bool $bound): int {

        $query = 'SELECT * from Distance WHERE internship_identifier = :idInternship AND id_teacher = :idTeacher';
        $stmt0 = $this->db->getConn()->prepare($query);
        $stmt0->bindParam(':idInternship', $internship_identifier);
        $stmt0->bindParam(':idTeacher', $id_teacher);
        $stmt0->execute();

        $minDuration = $stmt0->fetchAll(PDO::FETCH_ASSOC);
        if ($minDuration) {
            return $minDuration[0]['distance'];
        }

        $query = 'SELECT Address FROM Internship WHERE internship_identifier = :internship_identifier';
        $stmt1 = $this->db->getConn()->prepare($query);
        $stmt1->bindParam(':internship_identifier', $internship_identifier);
        $stmt1->execute();
        $addressInternship = $stmt1->fetch(PDO::FETCH_ASSOC);

        $query = 'SELECT Address FROM Has_address WHERE Id_teacher = :idTeacher';
        $stmt2 = $this->db->getConn()->prepare($query);
        $stmt2->bindParam(':idTeacher', $id_teacher);
        $stmt2->execute();
        $addressesTeacher = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $latLngStudent = $this->geocodeAddress($addressInternship['address']);

        $minDuration = PHP_INT_MAX;

        // On cherche l'addresse avec la distance la plus courte
        foreach ($addressesTeacher as $address) {
            $latLngTeacher = $this->geocodeAddress($address['address']);

            if (!$latLngStudent || !$latLngTeacher) {
                continue;
            }

            $duration = $this->calculateDuration($latLngStudent, $latLngTeacher);

            if ($duration < $minDuration) {
                (int) $minDuration = $duration;
            }
        }

        if (!$minDuration || $minDuration > 999999) {
            return 60;
        }

        if (!$bound) {
            $query = 'INSERT INTO Distance (id_teacher, internship_identifier, distance)
                  VALUES (:id_teacher, :id_internship, :distance)
                  ON CONFLICT (id_teacher, internship_identifier)
                  DO UPDATE SET distance = EXCLUDED.distance;';

            $stmt3 = $this->db->getConn()->prepare($query);
            $stmt3->bindParam(':id_teacher', $id_teacher);
            $stmt3->bindParam(':id_internship', $internship_identifier);
            $stmt3->bindParam(':distance', $minDuration);
            $stmt3->execute();
        }

        return $minDuration;
    }

    /**
     * Calcule la durée entre deux points avec OSRM
     * @param array $latLngStudent Latitude et longitude de l'origine
     * @param array $latLngTeacher Latitude et longitude de la destination
     * @return float|int|null Durée en minutes, ou null en cas d'erreur
     */
    private function calculateDuration(array $latLngStudent, array $latLngTeacher): float|int|null
    {
        $url = "http://router.project-osrm.org/route/v1/driving/{$latLngStudent['lng']},{$latLngStudent['lat']};{$latLngTeacher['lng']},{$latLngTeacher['lat']}?overview=false&alternatives=false&steps=false";

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

    /**
     * Géocode une adresse
     * @param string $address
     * @return array|null Contient latitude et longitude
     */
    private function geocodeAddress(string $address): ?array
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
}