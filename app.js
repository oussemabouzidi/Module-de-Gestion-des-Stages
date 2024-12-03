document.addEventListener('DOMContentLoaded', function () {
    const stageTypeSelect = document.getElementById('type_id');
    const encadrantDiv = document.querySelector('div.mb-3:nth-child(2)');
    const encadrantSelect = document.getElementById('encadrent_id');
    const jurySelect = document.getElementById('jury_id');
    const stagiaireSelect = document.getElementById('list_stagiaires');
    let table = document.getElementById('data-table');
    let rows = table.getElementsByTagName('tr');

    

    function handleStageTypeChange() {
    const selectedType = stageTypeSelect.value;

    // Show/hide encadrant and set multiple selection for stagiaires
    if (selectedType === 'PFE') {
        encadrantDiv.style.display = 'block';
        stagiaireSelect.multiple = true;
    } else {
        encadrantDiv.style.display = 'none';
        stagiaireSelect.multiple = false;
    }

    // Enable/disable encadrant options based on their number of stages
    if (selectedType === 'PFE') {
        Array.from(encadrantSelect.options).forEach(option => {
            const match = option.textContent.match(/nombre de stage (\d+)/);
            const encadrantNbrStages = match ? parseInt(match[1], 10) : null;

            if (encadrantNbrStages !== null && encadrantNbrStages < 4) {
                option.disabled = false;
            } else {
                option.disabled = true;
            }
        });
    }

    // Enable/disable jury options based on their number of stages
    Array.from(jurySelect.options).forEach(option => {
        const match = option.textContent.match(/nombre de stage: (\d+)/);
        const juryNbrStages = match ? parseInt(match[1], 10) : null;

        if (juryNbrStages !== null && juryNbrStages < 20) {
            option.disabled = false;
        } else {
            option.disabled = true;
        }
    });

    if (selectedType === 'PFE') {
        fetch('get_stagiaires.php?filieres=DSI3,MDW3') // Multiple filières in query
            .then(response => response.json())
            .then(data => {
                // Clear existing stagiaire options
                stagiaireSelect.innerHTML = '<option value="" selected disabled>Select stagiaire</option>';

                // Populate dropdown with filtered stagiaires
                data.forEach(stagiaire => {
                    const option = document.createElement('option');
                    option.value = stagiaire.id;
                    option.textContent = stagiaire.nom + ' ' + stagiaire.prenom;
                    stagiaireSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching stagiaires:', error));
    } else if(selectedType === 'initiation') {
            fetch("get_stagiaires.php?filieres=" + encodeURIComponent("technologie d'informatique"))
                .then(response => response.json())
                .then(data => {
                    // Clear existing stagiaire options
                    stagiaireSelect.innerHTML = '<option value="" selected disabled>Select stagiaire</option>';

                    // Populate dropdown with filtered stagiaires
                    data.forEach(stagiaire => {
                        const option = document.createElement('option');
                        option.value = stagiaire.id;
                        option.textContent = stagiaire.nom + ' ' + stagiaire.prenom;
                        stagiaireSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching stagiaires:', error));
    } else if(selectedType === 'perfectionnement'){
        fetch("get_stagiaires.php?filieres=DSI2,MDW2")
                .then(response => response.json())
                .then(data => {
                    // Clear existing stagiaire options
                    stagiaireSelect.innerHTML = '<option value="" selected disabled>Select stagiaire</option>';

                    // Populate dropdown with filtered stagiaires
                    data.forEach(stagiaire => {
                        const option = document.createElement('option');
                        option.value = stagiaire.id;
                        option.textContent = stagiaire.nom + ' ' + stagiaire.prenom;
                        stagiaireSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching stagiaires:', error));
    }else{
        stagiaireSelect.innerHTML = '<option value="" selected disabled>Select stagiaire</option>';

    }

}


    function validateStagiairesSelection(event) {
        const selectedType = stageTypeSelect.value;
        const selectedStagiaires = Array.from(stagiaireSelect.selectedOptions);

        if (selectedType === 'PFE' && selectedStagiaires.length > 3) {
            event.preventDefault();
            alert("Pour un stage PFE, vous pouvez sélectionner jusqu'à 3 stagiaires.");
            return false;
        } else if ((selectedType === 'initiation' || selectedType === 'perfectionnement') && selectedStagiaires.length > 1) {
            event.preventDefault();
            alert("Pour un stage d'initiation ou de perfectionnement, vous pouvez sélectionner uniquement 1 stagiaire.");
            return false;
        }
        return true;
    }

    stageTypeSelect.addEventListener('change', handleStageTypeChange);

    const stageForm = document.getElementById('stageForm');
    stageForm.addEventListener('submit', function(event) {
        validateStagiairesSelection(event);
    });

    handleStageTypeChange();


    document.querySelectorAll(".edit-stage").forEach(button => {
        button.addEventListener("click", function () {
            const stageId = this.getAttribute("data-id");
            loadStageData(stageId);
            const editModal = new bootstrap.Modal(document.getElementById("editStageModal"));
            editModal.show();
        });
    });

    document.querySelectorAll(".drop-stage").forEach(button => {
    button.addEventListener("click", function() {
        const id_stage = this.getAttribute("data-id");
        console.log(id_stage);

        // Create an AJAX request to delete the stage and related data
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_stage.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        // Send the stage_id to the PHP script
        xhr.send("stage_id=" + id_stage);

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = xhr.responseText;
                console.log(response); // Log the response from the server

                // You can also add logic to remove the row from the table
                // For example, remove the stage row from the table
                if (response === "success") {
                    alert("Stage deleted successfully!");
                    location.reload(); // Refresh the page or remove the row from DOM
                } else {
                    alert("Error deleting stage.");
                }
            }
        }
    });
});


    document.querySelectorAll(".termine_btn").forEach(button => {
    button.addEventListener("click", function() {
        const id_stage = this.getAttribute("data-id");
        const currentStatus = this.getAttribute("data-is-termine") === "1";
        const newStatus = currentStatus ? 0 : 1;

        // Update the button's data-is-termine attribute immediately
        this.setAttribute("data-is-termine", newStatus);
        this.innerHTML = `<i class='fas fa-check'></i> ${newStatus ? 'Terminé' : 'Non Terminé'}`;

        // AJAX request to update in the database
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "update_terminer.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("stage_id=" + id_stage + "&is_termine=" + newStatus);

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = xhr.responseText.trim();
                if (response === "success") {
                    alert("Stage status updated successfully!");
                } else {
                    alert("Error updating stage status: " + response);
                }
            } else {
                alert("Server error. Status code: " + xhr.status);
            }
        }
    });
});




    function loadStageData(stageId) {
    fetch(`get_stage.php?stage_id=${stageId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById("editStageId").value = data.id;
            document.getElementById("editStageType").value = data.type;
            console.log("Encadrant ID from data:", data.id_encadrant); // Log the returned encadrant ID
            document.getElementById("editEncadrantId").value = data.id_encadrant;
            document.getElementById("editDateSoutenance").value = data.date_soutenance;
            document.getElementById("editJuryId").value = data.id_jery;
            document.getElementById("intitule_id").value = data.intitule;

            // Set multiple selection for stagiaires
            const stagiairesSelect = document.getElementById("editStagiaires");
            Array.from(stagiairesSelect.options).forEach(option => {
                option.selected = data.stagiaires.includes(option.value);
            });
        })
        .catch(error => console.error("Error fetching stage data:", error));
    }




    window.saveStageEdits = function() {
    const formData = new FormData(document.getElementById("editStageForm"));
    formData.append("stage_id", document.getElementById("editStageId").value);

    // Get the selected stagiaires, filtering out any empty values
    const stagiaires = Array.from(document.querySelectorAll("[name='stagiaires[]']"))
        .map(input => input.value)
        .filter(value => value !== ""); // Filter out any empty values
    formData.append("stagiaires", JSON.stringify(stagiaires));

    // Remove any unwanted "stagiaires[]" entries from the FormData
    formData.delete("stagiaires[]");

    // Log form data to confirm all fields are included and correct
    for (const pair of formData.entries()) {
        console.log(`${pair[0]}: ${pair[1]}`);
    }

    fetch("update_stage.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Modifications enregistrées avec succès !");
            location.reload();
        } else {
            alert("Erreur lors de l'enregistrement des modifications.");
        }
    })
    .catch(error => console.error("Error updating stage:", error));
    }




    window.searchAll = function () {
    let nameInput = document.getElementById('searchStages').value.toLowerCase();
    let typeInput = document.getElementById('search_type').value.toLowerCase();
    let encadrantInput = document.getElementById('encadrent').value.toLowerCase();
    let dateInput = document.getElementById('dateSoutenance_id').value.toLowerCase();
    let juryInput = document.getElementById('jury_id').value.toLowerCase();


    // Loop through table rows (skip the header row)
    for (let i = 1; i < rows.length; i++) {
        let cells = rows[i].getElementsByTagName('td');

        let match = true;  // Assume the row matches all criteria

        // Check each column for a match with its respective input
        if (nameInput && !cells[0].innerText.toLowerCase().includes(nameInput)) {
            match = false;
        }
        if (typeInput && !cells[1].innerText.toLowerCase().includes(typeInput)) {
            match = false;
        }
        if (encadrantInput && !cells[3].innerText.toLowerCase().includes(encadrantInput)) {
            match = false;
        }
        if (dateInput && !cells[2].innerText.toLowerCase().includes(dateInput)) {
            match = false;
        }
        if (juryInput && !cells[4].innerText.toLowerCase().includes(juryInput)) {
            match = false;
        }

        // Show or hide the row based on whether it matches all criteria
        rows[i].style.display = match ? "" : "none";
    }
};


document.getElementById('resetButton').addEventListener('click', function(){
    document.getElementById('searchStages').value = '';
    document.getElementById('search_type').value = '';
    document.getElementById('encadrent').value = '';
    document.getElementById('dateSoutenance_id').value = '';
    document.getElementById('jury_id').value = '';
    
    window.searchAll();
})

document.getElementById("exportStageBtn").addEventListener("click", function(event) {
    event.preventDefault(); // Prevent the default form submission

    // Get input values
    var searchStages = document.getElementById("searchStages").value;
    var searchTypeStage = document.getElementById("search_type").value;
    var encadrent = document.getElementById("encadrent").value;
    var dateSoutenance = document.getElementById("dateSoutenance_id").value;
    var juryId = document.getElementById("jury_id").value;

    // Redirect to pdf.php with the form data as URL parameters
    window.location.href = "pdf.php?searchStages=" + encodeURIComponent(searchStages) + 
        "&searchTypeStage=" + encodeURIComponent(searchTypeStage) + 
        "&encadrent=" + encodeURIComponent(encadrent) + 
        "&dateSoutenance=" + encodeURIComponent(dateSoutenance) + 
        "&juryId=" + encodeURIComponent(juryId);
});

document.getElementById("exportExcelBtn").addEventListener("click", function(event) {
    event.preventDefault(); // Prevent default form submission

    // Get input values
    var searchStages = document.getElementById("searchStages").value;
    var searchTypeStage = document.getElementById("search_type").value;
    var encadrent = document.getElementById("encadrent").value;
    var dateSoutenance = document.getElementById("dateSoutenance_id").value;
    var juryId = document.getElementById("jury_id").value;

    // Redirect to excel.php with form data as URL parameters
    window.location.href = "excel.php?searchStages=" + encodeURIComponent(searchStages) +
        "&searchTypeStage=" + encodeURIComponent(searchTypeStage) +
        "&encadrent=" + encodeURIComponent(encadrent) +
        "&dateSoutenance=" + encodeURIComponent(dateSoutenance) +
        "&juryId=" + encodeURIComponent(juryId);
});


    
});

