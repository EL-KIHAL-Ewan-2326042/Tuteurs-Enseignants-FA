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
    public function getDepTeacher(string $identifier): false|array {
        $query = 'SELECT department_name
                    FROM teaches
                    WHERE  id_teacher = :teacher';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $identifier);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie un tableau contenant les stages des élèves du département passé en paramètre et leurs informations
     * @param string $department le département duquel les élèves sélectionnés font partie
     * @return false|array tableau contenant le numéro, le nom et le prénom de l'élève, ainsi que le nom de l'entreprise dans lequel il va faire son stage, le sujet et les dates, false sinon
     */
    public function getStudentsPerDepartment(string $department): false|array {
        $query = 'SELECT *
                    FROM student
                    JOIN study_at
                    ON student.student_number = study_at.student_number
                    JOIN internship
                    ON student.student_number = internship.student_number
                    WHERE department_name = :dep';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':dep', $department);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie un tableau contenant les informations de chaque tutorat terminé de l'élève passé en paramètre
     * @param string $student le numéro de l'étudiant dont on récupère les informations
     * @return false|array tableau contenant, pour chaque tutorat, le numéro d'enseignant du tuteur, le numéro de l'élève et les dates, false sinon
     */
    public function getInternships(string $student): false|array {
        $query = 'SELECT id_teacher, student_number, responsible_start_date, responsible_end_date
                    FROM is_responsible
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
        $query1 = "SELECT student_number, keywords
                    FROM internship
                    WHERE student_number = :studentId
                    AND start_date_internship > CURRENT_DATE";
        $stmt1 = $this->db->getConn()->prepare($query1);
        $stmt1->bindParam(':studentId', $studentId);
        $stmt1->execute();
        $result = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $searchTerm1 = "";

        for ($i = 0; $i < count($result); ++$i) {
            $searchTerm1 .= $result[$i]["keywords"];
            if ($i < count($result) - 1) $searchTerm1 .= " ";
        }

        $pdo = $this->db;
        $searchTerm1 = trim($searchTerm1);
        $tsQuery1 = implode(' | ', explode(' ', $searchTerm1));
        $tsQuery1 = implode(' & ', explode('_', $tsQuery1));

        $query2 = "SELECT discipline_name FROM is_taught WHERE id_teacher = :id";
        $stmt2 = $this->db->getConn()->prepare($query2);
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

        $query3 = "SELECT to_tsquery('french', :searchTerm1) AS keywords, to_tsquery('french', :searchTerm2) AS discip";
        $stmt3 = $this->db->getConn()->prepare($query3);
        $stmt3->BindValue(':searchTerm1', $tsQuery1);
        $stmt3->bindValue(':searchTerm2', $tsQuery2);
        $stmt3->execute();

        $result = $stmt3->fetch(PDO::FETCH_ASSOC);

        echo $tsQuery1 . " - ";
        echo $tsQuery2 . " -- ";
        if ($result) {
            echo $result['keywords'] . " ||| ";
            echo $result['discip'] . " ||| ";
        }

        if (!$result) return 0;
        return 0.5*5;

        $query1 = 'SELECT discipline_name FROM is_taught WHERE id_teacher = :id';
        $stmt1 = $this->db->getConn()->prepare($query1);
        $stmt1->bindParam(':id', $identifier);
        $stmt1->execute();
        $result = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $searchTerm = "";

        for($i = 0; $i < count($result); $i++) {
            $searchTerm .= $result[$i]['discipline_name'];
            if($i < count($result) - 1) $searchTerm .= '_';
        }

        $pdo = $this->db;
        $searchTerm = trim($searchTerm);
        $tsQuery = implode(' | ', explode('_', $searchTerm));

        $query2 = "SELECT student_number, keywords, to_tsvector('french', keywords) key, to_tsquery('french', :searchTerm) search, ts_rank_cd(to_tsvector('french', keywords), to_tsquery('french', :searchTerm), 32) AS rank
                    FROM internship
                    WHERE to_tsquery('french', :searchTerm) @@ to_tsvector('french', keywords)
                    AND student_number = :studentId
                    AND start_date_internship > CURRENT_DATE";

        $stmt2 = $pdo->getConn()->prepare($query2);
        $stmt2->bindValue(':searchTerm', $tsQuery);
        $stmt2->bindValue(':studentId', $studentId);
        $stmt2->execute();

        $result = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo $result['key'] . " ||| ";
            echo $result['search'] . " ||| ";
        }

        if (!$result) return 0;
        return $result["rank"]*5;
    }

    /**
     * Calcul de distance entre un eleve et un professeur
     * @param string $idStudent l'identifiant de l'eleve
     * @param string $idTeacher l'identifiant du professeur
     * @return int distance en minute entre les deux
     */
    public function getDistance(string $idStudent, string $idTeacher): int {
        $query = 'SELECT Address FROM Internship WHERE Student_number = :idStudent';
        $stmt1 = $this->db->getConn()->prepare($query);
        $stmt1->bindParam(':idStudent', $idStudent);
        $stmt1->execute();
        $addressStudent = $stmt1->fetch(PDO::FETCH_ASSOC);

        $query = 'SELECT Address FROM Has_address WHERE Id_teacher = :idTeacher';
        $stmt2 = $this->db->getConn()->prepare($query);
        $stmt2->bindParam(':idTeacher', $idTeacher);
        $stmt2->execute();
        $addressesTeacher = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $latLngStudent = $this->geocodeAddress($addressStudent['address']);

        $minDuration = PHP_INT_MAX;

        // On cherche l'addresse avec la distance la plus courte
        foreach ($addressesTeacher as $address) {
            $latLngTeacher = $this->geocodeAddress($address['address']);

            if (!$latLngStudent || !$latLngTeacher) {
                continue;
            }

            $duration = $this->calculateDuration($latLngStudent, $latLngTeacher);

            if ($duration < $minDuration) {
                $minDuration = $duration;
            }
        }

        return (int) $minDuration;
    }

    /**
     * Requete a l'api de google pour renvoyer la distance entre deux points
     * @param array $latLngStudent lattitude et longitude de l'origine
     * @param array $latLngTeacher longitude et longitude de l'origine
     * @return float|int|null distance en minute ou decimal. Renvoie null si erreur
     */
    private function calculateDuration(array $latLngStudent, array $latLngTeacher): float|int|null
    {
        $apiKey = 'AIzaSyCBS2OwTaG2rfupX3wA-DlTbsBEG9yDVKk';
        $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$latLngStudent['lat']},{$latLngStudent['lng']}&destination={$latLngTeacher['lat']},{$latLngTeacher['lng']}&key=" . $apiKey;

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if ($data['status'] === 'OK') {
            return $data['routes'][0]['legs'][0]['duration']['value'] / 60;
        }
        return null;
    }

    /**
     * Geocode une addresse
     * @param string $address
     * @return array|null contient lattitude et longitude
     */
    private function geocodeAddress(string $address): ?array
    {
        $apiKey = 'AIzaSyCBS2OwTaG2rfupX3wA-DlTbsBEG9yDVKk';
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if ($data['status'] === 'OK') {
            return [
                'lat' => $data['results'][0]['geometry']['location']['lat'],
                'lng' => $data['results'][0]['geometry']['location']['lng']
            ];
        }
        return null;
    }

}