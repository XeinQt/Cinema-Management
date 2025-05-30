let customerTable;
let currentFilter = "";

// Modal Functions
function openModal() {
    const modal = document.getElementById("addCustomerModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeModal() {
    const modal = document.getElementById("addCustomerModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// Initialize form submission
function initializeFormHandler() {
    document.getElementById("addCustomerForm").addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch(baseUrl() + "/CustomersManagement/create", {
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
                    text: data.message || 'Customer added successfully.'
                });

                this.reset();
                closeModal();
                
                // Refresh the table after adding a customer
                if (customerTable) {
                    customerTable.ajax.reload(null, false);
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: data.message || "Failed to add Customer.",
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
}


// Initialize customer table and form handlers
function initializeCustomerTable(filter = "") {
    currentFilter = filter;

    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#customerTable')) {
        $('#customerTable').DataTable().destroy();
    }

    customerTable = $('#customerTable').DataTable({
        ajax: {
            url: baseUrl() + '/CustomersManagement/DataTables',
            type: 'GET',
            data: function(d) {
                d.filter = filter;
                return d;
            }
        },
        processing: true,
        serverSide: true,
        scrollX: true,
        autoWidth: false,
        columns: [
            { 
                data: 'customer_id', 
                name: 'customer_id', 
                title: 'ID',
                width: '50px'
            },
            { 
                data: 'first_name', 
                name: 'first_name', 
                title: 'First Name',
                width: '120px'
            },
            { 
                data: 'last_name', 
                name: 'last_name', 
                title: 'Last Name',
                width: '120px'
            },
            { 
                data: 'email', 
                name: 'email', 
                title: 'Email',
                width: '200px'
            },
            { 
                data: 'phonenumber', 
                name: 'phonenumber', 
                title: 'Phone No.',
                width: '120px'
            },
            {
                data: 'active',
                name: 'active',
                title: 'Status',
                width: '100px',
                render: function(data) {
                    return data == 1
                        ? '<span class="px-2 py-1 bg-green-500 text-white rounded-full text-sm">Active</span>'
                        : '<span class="px-2 py-1 bg-red-500 text-white rounded-full text-sm">Inactive</span>';
                }
            },
            {
                data: null,
                title: 'Actions',
                width: '200px',
                orderable: false,
                className: 'text-left',
                render: function(data, type, row) {
                    let buttons = '<div class="flex space-x-2">';
                    
                    // Edit button - show for both active and inactive
                    buttons += `<button class="edit-customer inline-flex items-center bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded" data-id="${row.customer_id}">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>`;

                    // Show different buttons based on active status
                    if (row.active == 1) {
                        buttons += `<button class="delete-customer inline-flex items-center bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded" data-id="${row.customer_id}">
                            <i class="fas fa-trash mr-1"></i> Deactivate
                        </button>`;
                    } else {
                        buttons += `<button class="restore-customer inline-flex items-center bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded" data-id="${row.customer_id}">
                            <i class="fas fa-undo mr-1"></i> Restore
                        </button>`;
                    }
                    
                    buttons += '</div>';
                    return buttons;
                }
            }
        ],
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5], // Name, Email, Phone columns
                className: 'text-left'
            },
            {
                targets: -1, // Actions column
                className: 'text-left'
            }
        ],
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });

    // Add custom styling
    $('head').append(`
        <style>
            .dataTables_wrapper .dataTables_length {
                margin-bottom: 15px;
            }
            .dataTables_wrapper .dataTables_filter {
                margin-bottom: 15px;
            }
            #customerTable {
                width: 100% !important;
            }
            #customerTable th, #customerTable td {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .dataTables_scrollBody {
                min-height: 400px;
            }
            .dataTables_wrapper .dataTables_scroll {
                margin-bottom: 15px;
            }
            .dataTables_scrollHeadInner table {
                margin-bottom: 0 !important;
            }
            .dataTables_scrollBody::-webkit-scrollbar {
                height: 8px;
                width: 8px;
            }
            .dataTables_scrollBody::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 4px;
            }
            .dataTables_scrollBody::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 4px;
            }
            .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
        </style>
    `);

    // Initialize form submission handler
    initializeFormHandler();
}

// Handle filter change
document.getElementById("filter").addEventListener("change", function() {
    initializeCustomerTable(this.value);
});

// Handle delete customer
$(document).on('click', '.delete-customer', function() {
    const customerId = $(this).data('id');
    
    Swal.fire({
        title: 'Are you sure?',
        text: "This customer will be deactivated!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, deactivate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            updateCustomerStatus(customerId);
        }
    });
});

// Function to update customer status
async function updateCustomerStatus(customerId) {
    try {
        const response = await fetch(`${baseUrl()}/CustomersManagement/updateStatus/${customerId}`, {
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
            if (customerTable) {
                customerTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            error.message || 'Failed to update customer status',
            'error'
        );
    }
} 



function openEditModal() {
    const modal = document.getElementById("editCustomerModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeEditModal() {
    const modal = document.getElementById("editCustomerModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

$(document).on('click', '.edit-customer', function() {
    const customerId = $(this).data('id');
    const row = customerTable.row($(this).closest('tr')).data();
    
    // Populate the edit form
    document.getElementById('edit_customer_id').value = customerId;
    document.getElementById('edit_first_name').value = row.first_name;
    document.getElementById('edit_last_name').value = row.last_name;
    document.getElementById('edit_email').value = row.email;
    document.getElementById('edit_phonenumber').value = row.phonenumber;
    
    // Open the edit modal
    openEditModal();
});

document.getElementById("editCustomerForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const customerId = formData.get('customer_id');

    try {
        const response = await fetch(baseUrl() + `/CustomersManagement/update/${customerId}`, {
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
                text: data.message || 'Customer updated successfully.'
            });

            closeEditModal();
            
                // Refresh the table after updating a customer
            if (customerTable) {
                customerTable.ajax.reload(null, false);
            }
        } else {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: data.message || "Failed to update Customer.",
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

// Add restore functionality
$(document).on('click', '.restore-customer', function() {
    const customerId = $(this).data('id');
    
    Swal.fire({
        title: 'Restore Customer?',
        text: "This will reactivate the customer!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, restore it!'
    }).then((result) => {
        if (result.isConfirmed) {
            restoreCustomer(customerId);
        }
    });
});

// Function to restore customer
async function restoreCustomer(customerId) {
    try {
        const response = await fetch(`${baseUrl()}/CustomersManagement/restore/${customerId}`, {
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
                'Restored!',
                data.message,
                'success'
            );
            // Reload the DataTable to reflect the changes
            if (customerTable) {
                customerTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            error.message || 'Failed to restore customer',
            'error'
        );
    }
}