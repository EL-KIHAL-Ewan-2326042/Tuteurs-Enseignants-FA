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
     * @param string $identifier identifiant de l'enseignant
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
     * Renvoie un tableau contenant les stages des élèves du département passé en paramètre et leurs informations
     * @param string $department le département duquel les élèves sélectionnés font partie
     * @return false|array tableau contenant le numéro, le nom et le prénom de l'élève, ainsi que le nom de l'entreprise dans lequel il va faire son stage, le sujet et les dates, false sinon
     */
    public function getInternshipsPerDepartment(string $department): false|array {
        $query = 'SELECT *
                    FROM Internship
                    JOIN Student ON Internship.student_number = Student.student_number
                    JOIN study_at ON student.student_number = study_at.student_number
                    WHERE department_name = :department_name
                    AND id_teacher IS NULL';
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
                    WHERE student_number = :student';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':student', $student);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie un score associé à la pertinence entre le sujet de stage de l'élève et les disciplines enseignées par le professeur, tous deux passés en paramètre
     * @param string $studentId numéro d'élève
     * @param string $identifier identifiant de l'enseignant
     * @return float score associé à la pertinence entre le sujet de stage et les disciplines enseignées par le professeur connecté
     */
    public function scoreDiscipSubject(string $studentId, string $identifier): float {
        $pdo = $this->db;

        $query1 = "SELECT student_number, keywords
                    FROM internship
                    WHERE student_number = :studentId
                    AND start_date_internship > CURRENT_DATE";
        $stmt1 = $pdo->getConn()->prepare($query1);
        $stmt1->bindParam(':studentId', $studentId);
        $stmt1->execute();
        $result = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $searchTerm1 = "";

        for ($i = 0; $i < count($result); ++$i) {
            $searchTerm1 .= $result[$i]["keywords"];
            if ($i < count($result) - 1) $searchTerm1 .= " ";
        }

        $searchTerm1 = trim($searchTerm1);
        $tsQuery1 = implode(' | ', explode(' ', $searchTerm1));
        $tsQuery1 = implode(' & ', explode('_', $tsQuery1));

        $query2 = "SELECT discipline_name FROM is_taught WHERE id_teacher = :id";
        $stmt2 = $pdo->getConn()->prepare($query2);
        $stmt2->bindParam(':id', $identifier);
        $stmt2->execute();
        $result = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $searchTerm2 = "";

        for($i = 0; $i < count($result); ++$i) {
            $searchTerm2 .= $result[$i]['discipline_name'];
            if($i < count($result) - 1) $searchTerm2 .= ' ';
        }

        $searchTerm2 = trim($searchTerm2);
        $tsQuery2 = implode(' | ', explode(' ', $searchTerm2));
        $tsQuery2 = implode(' & ', explode('_', $tsQuery2));

        $query3 = "SELECT to_tsquery('french', :searchTerm1) AS internship, to_tsquery('french', :searchTerm2) AS discip";
        $stmt3 = $pdo->getConn()->prepare($query3);
        $stmt3->BindValue(':searchTerm1', $tsQuery1);
        $stmt3->bindValue(':searchTerm2', $tsQuery2);
        $stmt3->execute();

        $result = $stmt3->fetch(PDO::FETCH_ASSOC);

        $internship = explode(' | ', $result['internship']);
        $disciplines = explode(' | ', $result['discip']);

        if (count($internship) === 0 || count($disciplines) === 0) return 0;

        $score = 0;

        foreach ($internship as $subject) {
            $subj = explode(' & ', $subject);
            foreach ($disciplines as $discipline) {
                if ($subject == $discipline) $score += 1/count($internship);
                else {
                    foreach ($subj as $sub) {
                        foreach (explode(' & ', $discipline) as $discip) {
                            if ($discip == $sub) $score += 1/(count($internship)*count($subj));
                        }
                    }
                } if ($score === 1) break;
            } if ($score === 1) break;
        }

        return $score;
    }

    /**
     * Calcul de distance entre un eleve et un professeur
     * @param string $idStudent l'identifiant de l'eleve
     * @param string $idTeacher l'identifiant du professeur
     * @return int distance en minute entre les deux
     */
    public function getDistance(string $internship_identifier, string $id_teacher): int {

        $query = 'SELECT * from Distance WHERE internship_identifier = :idInternship AND id_teacher = :idTeacher';
        $stmt0 = $this->db->getConn()->prepare($query);
        $stmt0->bindParam(':idTeacher', $id_teacher);
        $stmt0->bindParam(':idInternship', $internship_identifier);
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

        $query = 'INSERT INTO Distance VALUES (:id_teacher, :id_internship, :distance)';
        $stmt3 = $this->db->getConn()->prepare($query);
        $stmt3->bindParam(':id_teacher', $id_teacher);
        $stmt3->bindParam(':id_internship', $internship_identifier);
        $stmt3->bindParam(':distance', $minDuration);
        $stmt3->execute();

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
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        if (isset($data['routes'][0]['duration'])) {
            $duration = round($data['routes'][0]['duration'] / 60);
        }
        else {
            return null;
        }

        if ($duration >= 9223372036854775807) {
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
        $response = file_get_contents($url, false, $context);
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