<?php

namespace Blog\Models;

use Database;
use PDO;

class GlobalModel {
    private Database $db;
    public function __construct(Database $db){
        $this->db = $db;
    }

    public function getDepTeacher($identifier): false|array {
        $query = 'SELECT department_name
                    FROM teaches
                    WHERE  id_teacher = :teacher';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $identifier);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
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
    public function getInternships(string $student): false|array {
        $query = 'SELECT id_teacher, student_number, responsible_start_date, responsible_end_date
                    FROM is_responsible
                    WHERE student_number = :student';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':student', $student);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function scoreDiscipSubject(string $studentId, string $identifier): float {
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

        $query2 = "SELECT student_number, keywords, ts_rank_cd(to_tsvector('french', keywords), to_tsquery('french', :searchTerm), 32) AS rank
                    FROM internship
                    WHERE to_tsquery('french', :searchTerm) @@ to_tsvector('french', keywords)
                    AND student_number = :studentId
                    AND start_date_internship > CURRENT_DATE";

        $stmt2 = $pdo->getConn()->prepare($query2);
        $stmt2->bindValue(':searchTerm', $tsQuery);
        $stmt2->bindValue(':studentId', $studentId);
        $stmt2->execute();

        $result = $stmt2->fetch(PDO::FETCH_ASSOC);

        if (!$result) return 0;
        return $result["rank"]*5;
    }

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