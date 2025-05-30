let cinemaTable;

const dropdownCache = {
    malls: null,
    managers: null
};

function initializeCinemaTable() {
    if ($.fn.DataTable.isDataTable('#cinemasDatatables')) {
        $('#cinemasDatatables').DataTable().destroy();
    }

    cinemaTable = $('#cinemasDatatables').DataTable({
        ajax: baseUrl() + '/CinemasManagement/DataTables',
        processing: true,
        serverSide: true,
        columns: [
            { data: 'cinema_id', name: 'cinema_id', title: 'Cinema ID' },
            { data: 'mall_id', name: 'mall_id', title: 'Mall ID' },
            { data: 'manager_id', name: 'manager_id', title: 'Manager ID' },
            { data: 'name', name: 'name', title: 'Name' },
            {
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    return getActionButtons(row.cinema_id, 'cinema');
                }
            }
        ]
    });
}

$(document).on('click', '.delete-cinema', function() {
    const cinemaId = $(this).data('id');
    
    Swal.fire({
        title: 'Are you sure?',
        text: "This cinema will be deactivated!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, deactivate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            updateCinemaStatus(cinemaId);
        }
    });
});

async function updateCinemaStatus(cinemaId) {
    try {
        const response = await fetch(`${baseUrl()}/CinemasManagement/updateStatus/${cinemaId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire(
                'Deactivated!',
                data.message,
                'success'
            );
            if (cinemaTable) {
                cinemaTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            error.message || 'Failed to update cinema status',
            'error'
        );
    }
}

function openModal() {
    const modal = document.getElementById("addCinemaModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    populateDropdowns();
}

function closeModal() {
    const modal = document.getElementById("addCinemaModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// Function to populate mall and manager dropdowns
async function populateDropdowns() {
    try {
        // Fetch malls
        const mallsResponse = await fetch('/MallsManagement/DataTables');
        const mallsData = await mallsResponse.json();
        const mallSelect = document.getElementById('mall_select');
        
        // Clear existing options except the first one
        while (mallSelect.options.length > 1) {
            mallSelect.remove(1);
        }
        
        mallsData.data.forEach(mall => {
            const option = document.createElement('option');
            option.value = mall.name;  // Using name as value since CinemasController expects name
            option.textContent = mall.name;
            mallSelect.appendChild(option);
        });

        // Fetch managers
        const managersResponse = await fetch('/ManagersManagement/DataTables');
        const managersData = await managersResponse.json();
        const managerSelect = document.getElementById('manager_select');
        
        // Clear existing options except the first one
        while (managerSelect.options.length > 1) {
            managerSelect.remove(1);
        }
        
        managersData.data.forEach(manager => {
            const option = document.createElement('option');
            option.value = manager.first_name + ' ' + manager.last_name;  // Full name as value
            option.textContent = manager.first_name + ' ' + manager.last_name;
            managerSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading dropdowns:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load mall and manager data'
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initializeCinemaTable();
    
    document.getElementById("addCinemaForm")?.addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        try {
            const response = await fetch(baseUrl() + "/CinemasManagement/create", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json",
                    // Don't set Content-Type when using FormData, let the browser set it with the boundary
                },
                body: formData,
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Cinema added successfully.'
                });

                this.reset();
                closeModal();
                if (cinemaTable) {
                    cinemaTable.ajax.reload(null, false);
                }
            } else {
                let errorMessage = data.message || "Failed to add Cinema.";
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join('\n');
                }
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: errorMessage,
                });
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            Swal.fire({
                icon: "error",
                title: "Unexpected Error",
                text: "Network error or server is not responding. Please try again.",
            });
        }
    });
});

function openEditModal() {
    const modal = document.getElementById("editCinemaModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    populateEditDropdowns();
}

function closeEditModal() {
    const modal = document.getElementById("editCinemaModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// Function to populate edit form dropdowns
async function populateEditDropdowns() {
    try {
        // Fetch malls
        const mallsResponse = await fetch('/MallsManagement/DataTables');
        const mallsData = await mallsResponse.json();
        const mallSelect = document.getElementById('edit_mall_select');
        
        // Clear existing options except the first one
        while (mallSelect.options.length > 1) {
            mallSelect.remove(1);
        }
        
        mallsData.data.forEach(mall => {
            const option = document.createElement('option');
            option.value = mall.mall_id;
            option.textContent = mall.name;
            mallSelect.appendChild(option);
        });

        // Fetch managers
        const managersResponse = await fetch('/ManagersManagement/DataTables');
        const managersData = await managersResponse.json();
        const managerSelect = document.getElementById('edit_manager_select');
        
        // Clear existing options except the first one
        while (managerSelect.options.length > 1) {
            managerSelect.remove(1);
        }
        
        managersData.data.forEach(manager => {
            const option = document.createElement('option');
            option.value = manager.manager_id;
            option.textContent = manager.first_name + ' ' + manager.last_name;
            managerSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading dropdowns:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load mall and manager data'
        });
    }
}

// Handle edit button click
$(document).on('click', '.edit-cinema', function() {
    const cinemaId = $(this).data('id');
    const row = cinemaTable.row($(this).closest('tr')).data();
    
    // Populate the edit form
    document.getElementById('edit_cinema_id').value = cinemaId;
    document.getElementById('edit_cinema_name').value = row.name;
    
    // Open the edit modal and populate dropdowns
    openEditModal();
    
    // Set the selected values after dropdowns are populated
    setTimeout(() => {
        const mallSelect = document.getElementById('edit_mall_select');
        const managerSelect = document.getElementById('edit_manager_select');
        
        // Find and select the mall option
        Array.from(mallSelect.options).forEach(option => {
            if (option.value == row.mall_id) {
                option.selected = true;
            }
        });
        
        // Find and select the manager option
        Array.from(managerSelect.options).forEach(option => {
            if (option.value == row.manager_id) {
                option.selected = true;
            }
        });
    }, 500); // Give time for dropdowns to populate
});

// Handle edit form submission
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById("editCinemaForm")?.addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const cinemaId = formData.get('cinema_id');

        try {
            const response = await fetch(`${baseUrl()}/CinemasManagement/update/${cinemaId}`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json"
                },
                body: formData,
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Cinema updated successfully.'
                });

                closeEditModal();
                
                if (cinemaTable) {
                    cinemaTable.ajax.reload(null, false);
                }
            } else {
                let errorMessage = data.message || "Failed to update Cinema.";
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join('\n');
                }
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: errorMessage,
                });
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            Swal.fire({
                icon: "error",
                title: "Unexpected Error",
                text: "Network error or server is not responding. Please try again.",
            });
        }
    });
});