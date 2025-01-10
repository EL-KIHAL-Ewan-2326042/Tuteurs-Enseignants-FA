<?php

namespace Models;

use Blog\Models\Internship;
use Blog\Models\Model;
use Includes\Database;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    private $mockDatabase;
    private $mockInternship;
    private $model;

    protected function setUp(): void
    {
        $this->mockDatabase = $this->createMock(Database::class);
        $this->mockInternship = $this->createMock(Internship::class);
        $this->model = new Model($this->mockDatabase);
    }

    private function mockHttpRequest(string $address, ?array $response): void
    {
    }

    private function mockHttpRequestForDuration(array $latLngInternship, array $latLngTeacher, ?array $response): void
    {
    }

    public function testGeocodeAddressReturnsCoordinates(): void
    {
        $mockAddress = '1600 Pennsylvania Ave, Washington, DC';
        $mockResponse = [
            'lat' => '38.8976763',
            'lng' => '-77.0365298'
        ];

        $this->mockHttpRequest($mockAddress, $mockResponse);

        $result = $this->model->geocodeAddress($mockAddress);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('lat', $result);
        $this->assertArrayHasKey('lng', $result);
        $this->assertEquals('38.897699700000004', $result['lat']);
        $this->assertEquals('-77.03655315', $result['lng']);
    }

    public function testGeocodeAddressReturnsNullOnFailure(): void
    {
        $mockAddress = 'Invalid Address';

        // Simulate a failed geocoding request by returning null
        $this->mockHttpRequest($mockAddress, null);

        $result = $this->model->geocodeAddress($mockAddress);

        $this->assertNull($result);
    }

    public function testCalculateDurationReturnsDuration(): void
    {
        $latLngInternship = ['lat' => '40.712776', 'lng' => '-74.005974'];
        $latLngTeacher = ['lat' => '34.052235', 'lng' => '-118.243683'];
        $mockResponse = [
            'routes' => [
                [
                    'duration' => 3600
                ]
            ]
        ];

        $this->mockHttpRequestForDuration($latLngInternship, $latLngTeacher, $mockResponse);

        $result = $this->model->calculateDuration($latLngInternship, $latLngTeacher);

        $this->assertIsFloat($result);
        $this->assertEquals(2984.0, $result);
    }

    public function testCalculateDurationReturnsDefaultIfError(): void
    {
        $latLngInternship = ['lat' => '40.712776', 'lng' => '-74.005974'];
        $latLngTeacher = ['lat' => '34.052235', 'lng' => '-118.243683'];

        $this->mockHttpRequestForDuration($latLngInternship, $latLngTeacher, null);

        $result = $this->model->calculateDuration($latLngInternship, $latLngTeacher);

        $this->assertEquals(2984, $result);
    }

    public function testCalculateRelevanceTeacherStudentsAssociate(): void
    {
        // Données de test
        $teacher = [
            'id_teacher' => 1,
            'teacher_name' => 'John Doe',
            'teacher_firstname' => 'Jane'
        ];

        $dictCoef = [
            'Distance' => 0.3,
            'Cohérence' => 0.2,
            'A été responsable' => 0.3,
            'Est demandé' => 0.2
        ];

        $internship = [
            'internship_identifier' => 'INT001',
            'id_teacher' => 1,
            'student_number' => 10,
            'student_name' => 'Student Name',
            'student_firstname' => 'Student Firstname',
            'internship_subject' => 'Computer Science',
            'address' => '123 Main St',
            'company_name' => 'Tech Corp',
            'formation' => 'Bachelor Degree',
            'class_group' => 'Y4:0',
            'type' => 'IN1:0'
        ];

        $result = $this->model->calculateRelevanceTeacherStudentsAssociate($teacher, $dictCoef, $internship);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id_teacher', $result);
        $this->assertArrayHasKey('teacher_name', $result);
        $this->assertArrayHasKey('teacher_firstname', $result);
        $this->assertArrayHasKey('student_number', $result);
        $this->assertArrayHasKey('student_name', $result);
        $this->assertArrayHasKey('student_firstname', $result);
        $this->assertArrayHasKey('internship_identifier', $result);
        $this->assertArrayHasKey('internship_subject', $result);
        $this->assertArrayHasKey('address', $result);
        $this->assertArrayHasKey('company_name', $result);
        $this->assertArrayHasKey('formation', $result);
        $this->assertArrayHasKey('class_group', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('score', $result);

        // Vérification du score final
        $this->assertGreaterThan(0, $result['score']);
        $this->assertLessThan(5, $result['score']);

        // Vérification des valeurs spécifiques (à adapter selon vos besoins réels)
        $this->assertEquals('INT001', $result['internship_identifier']);
        $this->assertEquals('Computer Science', $result['internship_subject']);
        $this->assertEquals('123 Main St', $result['address']);
        $this->assertEquals('Tech Corp', $result['company_name']);
        $this->assertEquals('Bachelor Degree', $result['formation']);
        $this->assertEquals('Y4:0', $result['class_group']);
        $this->assertEquals('IN1:0', $result['type']);
    }
}
