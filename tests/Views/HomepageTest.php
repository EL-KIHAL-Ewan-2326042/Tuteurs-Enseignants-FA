<?php

namespace Views;

use Blog\Views\Homepage;
use Blog\Models\Homepage as HomepageModel;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Classe de HomepageTest
 *
 * Test de la vue Homepage : s'assure que la méthode showView()
 * fonctionne comme prévu
 */
class HomepageTest extends TestCase{
    private Homepage $view;
    private $mockModel;

    /**
     * Initialise la vue Homepage
     * @throws Exception
     */
    protected function setUp(): void{
        parent::setUp();
        $_SESSION = [];
        $this->mockModel = $this->createMock(HomepageModel::class);
        $this->view = new Homepage($this->mockModel);
    }

    /**
     * Test de la méthode showView() de la vue Dispatcher
     * sans étudiant sélectionné
     * @return void
     * @throws Exception
     */
    public function testShowViewNoStudentSelected() {
        //simulation d'une session avec aucun étudiant sélectionné
        $_SESSION = [];

        $this->mockModel->method('getInternships')->with('')->willReturn([]);

        //obtention du contenu généré
        ob_start();
        $this->view->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        //$this->assertStringNotContainsString('<h4 class="left-align"> Résultat pour: ', $output);
        $this->assertStringContainsString('Cet étudiant n\'a pas de stage ...', $output);
    }

    /**
     * Test de la méthode showView() de la vue Dispatcher
     * avec un étudiant sans adresse
     * @return void
     * @throws Exception
     */
    public function testShowViewStudentSelectedNoAddress(){
        //simulation d'une session avec un étudiant sans adresse
        $_SESSION['selected_student'] = [
            'student_number' => '12345',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'address' => ''
        ];

        $this->mockModel->method('getInternships')->with('12345')->willReturn([]);

        //obtention du contenu généré
        ob_start();
        $this->view->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('<h4 class="left-align"> Résultat pour: John Doe</h4>', $output);
        $this->assertStringContainsString("Cet étudiant n'a pas de stage ...", $output);
    }

    /**
     * Test de la méthode showView() de la vue Dispatcher
     * avec un étudiant ayant une adresse
     * @return void
     * @throws Exception
     */
    public function testShowViewStudentSelectedAddress(){
        //simulation d'une session avec un étudiant ayant une adrresse
        $_SESSION['selected_student'] = [
            'student_number' => '12345',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'address' => '123 Main St, Marseille'
        ];

        $this->mockModel->method('getInternships')->with('12345')->willReturn([
            ['id_teacher' => 1, 'student_number' => '12345', 'responsible_start_date' => '2023-01-01', 'responsible_end_date' => '2023-06-01']
        ]);

        //obtention du contenu généré
        ob_start();
        $this->view->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('<h4 class="left-align"> Résultat pour: John Doe</h4>', $output);
        $this->assertStringContainsString('123 Main St, Marseille', $output);
        $this->assertStringContainsString('2023-06-01', $output);
    }

    /**
     * Test de la méthode showView() de la vue Dispatcher
     * avec un étudiant et les départements disponibles
     * @return void
     * @throws Exception
     */
    public function testShowViewDepartmentsAvailable(){
        //simulation d'une session avec un étudiant et les départements disponibles
        $_SESSION['selected_student'] = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'address' => '123 Main St, Marseille'
        ];

        //Mock des départements
        $mockModel = $this->createMock(HomepageModel::class);
        $mockModel->method('getDepTeacher')->willReturn([
            ['department_name' => 'Informatique'],
            ['department_name' => 'Gestion'],
        ]);

        $this->mockModel->method('getDepTeacher')->willReturn([
            ['department_name' => 'Informatique'],
            ['department_name' => 'Gestion'],
        ]);

        //obtention du contenu généré
        ob_start();
        $this->view->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('Sélectionnez le(s) département(s) :', $output);
        $this->assertStringContainsString('Informatique', $output);
        $this->assertStringContainsString('Gestion', $output);
    }

    public function testShowViewDepartmentsNonAvailable() {
        //simulation d'une session avec un étudiant et les départements indisponibles
        $_SESSION['selected_student'] = [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'address' => '456 Another St, Marseille'
        ];

        //Mock des départements vides
        $mockModel = $this->createMock(HomepageModel::class);
        $mockModel->method('getDepTeacher')->willReturn([]);
        $this->view = new Homepage($mockModel);

        //obtention du contenu généré
        ob_start();
        $this->view->showView();
        $output = ob_get_clean();

        //vérification de l'affichage du contenu
        $this->assertStringContainsString('Vous ne faîtes partie d\'aucun département', $output);
    }


}