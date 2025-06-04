<?php
namespace Blog\Views\dashboard;

class Association
{
    public function __construct()
    {
    }

    public function showView(): void
    {
        ?>
        <div class="row">
            <h3>Association enseignant - étudiant</h3>
            <div class="card-panel white z-depth-3">
                <form action="./dispatcher"
                      method="post" id="associate-form">
                    <input type="hidden" name="action" value="associate_student_internship">
                    <p> Danger : Associe un enseignant à un stage
                        (ne prend pas en compte le nombre maximum d'étudiant,
                        mais le fait que le stage soit déjà attribué</p>
                    
                    <div class="row">
                        <div class="col s12">
                            <p class="helper-text">Cette action permet d'attribuer un tuteur à un étudiant.</p>
                        </div>

                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">school</i>
                            <input id="searchTeacher" name="searchTeacher"
                                   type="text" class="validate"
                                   placeholder="<?php htmlspecialchars($_SESSION['identifier'])?>">
                            <label for="searchTeacher"
                            >
                            Enseignant</label>
                        </div>

                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">work</i>
                            <input id="searchInternship" name="searchInternship"
                                   type="text" class="validate"
                                   required>
                            <label for="searchInternship">Numéro de stage ou d'alternance</label>
                        </div>

                        <div class="col s12 center-align">
                            <button class="btn waves-effect waves-light"
                                    type="submit"
                                    name="action"
                                    data-position="top"
                                    data-tooltip="Valider l'association">
                                Associer
                                <i class="material-icons right">link</i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
        <?php
    }
}
