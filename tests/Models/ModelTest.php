<?php
/**
 * Fichier contenant les test PHPUnit du modèle Model
 *
 * PHP version 8.3
 *
 * @category Models
 * @package  TutorMap/tests/Models
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
namespace Models;

use Blog\Models\Internship;
use Blog\Models\Model;
use includes\Database;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe gérant les tests PHPUnit du modèle Model
 *
 * PHP version 8.3
 *
 * @category Controller
 * @package  TutorMap/modules/Controllers
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class ModelTest extends TestCase
{
    private Database $_mockDatabase;
    private Internship $_mockInternship;
    private Model $_model;
    private Model $_mockModel;


    /**
     * Permet de d'initialiser les variables nécessaires pour les tests
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->_mockDatabase = $this->createMock(Database::class);
        $this->_mockInternship = $this->createMock(Internship::class);
        $this->_model = new Model($this->_mockDatabase);
        $this->_mockModel = $this->createMock(Model::class);
    }

    /**
     * Méthode privée permettant de mock des requetes http
     *
     * @param string     $address  l'addresse web
     * @param array|null $response la réponse
     *
     * @return void
     */
    private function _mockHttpRequest(string $address, ?array $response): void
    {
    }

    /**
     * Méthode privée permettant de mock des requêtes http
     * pour une durée
     *
     * @param array      $latLngInternship latitude et longitude du stage/alternance
     * @param array      $latLngTeacher    latitude et longitude du professeur
     * @param array|null $response         la
     *                                     réponse
     *
     * @return void
     */
    private function _mockHttpRequestForDuration(
        array $latLngInternship, array $latLngTeacher, ?array $response
    ): void {
    }

    /**
     * Test de la méthode geocodeAddress, vérifier que ça
     * renvoie bien des coordonnées
     *
     * @return void
     */
    public function testGeocodeAddressReturnsCoordinates(): void
    {
        $mockAddress = '1600 Pennsylvania Ave, Washington, DC';
        $mockResponse = [
            'lat' => '38.8976763',
            'lng' => '-77.0365298'
        ];

        $this->_mockHttpRequest($mockAddress, $mockResponse);

        $result = $this->_model->geocodeAddress($mockAddress);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('lat', $result);
        $this->assertArrayHasKey('lng', $result);
        $this->assertEquals('38.897699700000004', $result['lat']);
        $this->assertEquals('-77.03655315', $result['lng']);
    }

    /**
     * Test de la méthode geocodeAddress, vérifier que ça
     * renvoie bien null si erreur
     *
     * @return void
     */
    public function testGeocodeAddressReturnsNullOnFailure(): void
    {
        $mockAddress = 'Invalid Address';

        $this->_mockHttpRequest($mockAddress, null);

        $result = $this->_model->geocodeAddress($mockAddress);

        $this->assertNull($result);
    }

    /**
     * Test de la méthode calculateDuration, vérifier que ça renvoie
     * bien une durée
     *
     * @return void
     */
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

        $this->_mockHttpRequestForDuration(
            $latLngInternship, $latLngTeacher, $mockResponse
        );

        $result = $this->_model->calculateDuration(
            $latLngInternship, $latLngTeacher
        );

        $this->assertIsFloat($result);
        $this->assertEquals(2984.0, $result);
    }

    /**
     * Test de la méthode calculateDuration, vérifier que ça renvoie
     * une durée de base si erreur
     *
     * @return void
     */
    public function testCalculateDurationReturnsDefaultIfError(): void
    {
        $latLngInternship = ['lat' => '40.712776', 'lng' => '-74.005974'];
        $latLngTeacher = ['lat' => '34.052235', 'lng' => '-118.243683'];

        $this->_mockHttpRequestForDuration($latLngInternship, $latLngTeacher, null);

        $result = $this->_model->calculateDuration(
            $latLngInternship, $latLngTeacher
        );

        $this->assertEquals(2984, $result);
    }

    /**
     * Vérifier la méthode valudateHeader renvoie bien false si
     * un mauvais header est fourni
     *
     * @return void
     * @throws \Exception
     */
    public function testValidateHeadersReturnsFalseForBadHeader(): void
    {
        $validHeaders = ['id', 'name', 'email'];
        $tableName = 'user';

        $this->_mockModel->method('getTableColumn')->willReturn(
            ['id', 'name', 'email']
        );

        $result = $this->_mockModel->validateHeaders($validHeaders, $tableName);

        $this->assertFalse($result);
    }

    /**
     * Vérifier la méthode valudateHeader renvoie bien false si
     * un header avec plus de colonnes est fourni
     *
     * @return void
     * @throws \Exception
     */
    public function testValidateHeadersReturnsFalseForTeacherExtraColumns(): void
    {
        $validTeacherHeaders = ['id', 'name', 'address$type', 'discipline_name'];
        $tableName = 'teacher';

        $this->_mockModel->method('getTableColumn')->willReturn(['id', 'name']);

        $result = $this->_mockModel->validateHeaders(
            $validTeacherHeaders, $tableName
        );

        $this->assertFalse($result);
    }
}
