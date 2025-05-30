let mallTable;

function initializeMallTable() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#mallsDatatables')) {
        $('#mallsDatatables').DataTable().destroy();
    }

    mallTable = $('#mallsDatatables').DataTable({
        ajax: baseUrl() + '/MallsManagement/DataTables',
        processing: true,
        serverSide: true,
        columns: [
            { data: 'mall_id', name: 'mall_id', title: 'Mall ID' },
            { data: 'name', name: 'name', title: 'Name' },
            { data: 'location', name: 'location', title: 'Location' },
            { data: 'description', name: 'description', title: 'Description' },
            {
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    return getActionButtons(row.mall_id, 'mall');
                }
            }
        ]
    });
}



// Handle delete mall
$(document).on('click', '.delete-mall', function() {
    const mallId = $(this).data('id');
    
    Swal.fire({
        title: 'Are you sure?',
        text: "This mall will be deactivated!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, deactivate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            updateMallStatus(mallId);
        }
    });
});

// Function to update mall status
async function updateMallStatus(mallId) {
    try {
        const response = await fetch(`${baseUrl()}/MallsManagement/updateStatus/${mallId}`, {
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
            // Reload the DataTable to reflect the changes
            if (mallTable) {
                mallTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            error.message || 'Failed to update mall status',
            'error'
        );
    }
}

// Modal Functions
function openModal() {
    const modal = document.getElementById("addMallModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeModal() {
    const modal = document.getElementById("addMallModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}


// Initialize form submissions
document.addEventListener('DOMContentLoaded', function() {
    // Add Mall Form
    document.getElementById("addMallForm").addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch(baseUrl() + "/MallsManagement/create", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json();

            if (response.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Mall added successfully.'
                });

                this.reset();
                closeModal();
                
                // Refresh the table after adding a mall
                if (mallTable) {
                    mallTable.ajax.reload(null, false);
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: data.message || "Failed to add Mall.",
                });
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            Swal.fire({
                icon: "error",
                title: "Unexpected Error",
                text: "Something went wrong. Please try again.",
            });
        }
    });
});

function closeEditModal() {
    const modal = document.getElementById("editMallModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// diri ang edut
function openEditModal() {
    const modal = document.getElementById("editMallModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}


// Handle edit mall
$(document).on('click', '.edit-mall', function() {
    const mallId = $(this).data('id');
    const row = mallTable.row($(this).closest('tr')).data();
    
    // Populate the edit form
    document.getElementById('edit_mall_id').value = mallId;
    document.getElementById('edit_name').value = row.name;
    document.getElementById('edit_location').value = row.location;
    document.getElementById('edit_description').value = row.description;
    
    // Open the edit modal
    openEditModal();
});


// Edit Mall Form
document.getElementById("editMallForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const mallId = formData.get('mall_id');

    try {
        const response = await fetch(baseUrl() + `/MallsManagement/update/${mallId}`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                Accept: "application/json",
            },
            body: formData,
        });

        const data = await response.json();

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message || 'Mall updated successfully.'
            });

            closeEditModal();
            
            // Refresh the table after updating a mall
            if (mallTable) {
                mallTable.ajax.reload(null, false);
            }
        } else {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: data.message || "Failed to update Mall.",
            });
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        Swal.fire({
            icon: "error",
            title: "Unexpected Error",
            text: "Something went wrong. Please try again.",
        });
    }
});