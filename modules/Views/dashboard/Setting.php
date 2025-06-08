<?php
namespace Blog\Views\dashboard;

class Setting
{
    public function __construct()
    {
    }

    public function showView(): void
    {
        ?>
        <div class="row">
            <h3>Paramétrage enseignants</h3>
            <div class="card-panel white z-depth-3">
                <form action="/api/update-teacher-capacity" method="post" id="parametre-form">

                    <input type="hidden" name="action" value="associate_student_internship">

                    <div class="row">
                        <div class="col s12">
                            <p class="helper-text">Cette action permet d'attribuer de définir le nombre maximal d'alternants et de stagiaires qu'un enseignant donné peut tutorer.</p>
                        </div>

                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">school</i>
                            <input id="searchTeacher" name="searchTeacher"
                                   type="text" class="validate"
                                   placeholder="<?php echo htmlspecialchars($_SESSION['identifier'] ?? ''); ?>">
                            <label for="searchTeacher">Enseignant</label>
                        </div>

                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">group</i>
                            <input id="maxInterns" name="maxInterns" type="number" min="0" required>
                            <label for="maxInterns">Nombre max. de stagiaires</label>
                        </div>

                        <div class="input-field col s12 m6">
                            <i class="material-icons prefix">business_center</i>
                            <input id="maxApprentices" name="maxApprentices" type="number" min="0" required>
                            <label for="maxApprentices">Nombre max. d'alternants</label>
                        </div>

                        <div class="col s12 center-align">
                            <button class="btn waves-effect waves-light"
                                    type="submit"
                                    name="action"
                                    data-position="top"
                                    data-tooltip="Valider l'association">
                                Modifier
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
?>