let bookingTable = null;
let currentActiveFilter = "";
let currentStatusFilter = "";
let isInitialized = false;

// Initialize dropdownCache at global scope
const dropdownCache = {
    cinemas: null,
    movies: null
};

function baseUrl() {
    return location.protocol + "//" + location.host + "";
}

function initializeBookingTable(activeFilter = "", statusFilter = "") {
    // If table is already initialized with the same filters, don't reinitialize
    if (isInitialized && currentActiveFilter === activeFilter && currentStatusFilter === statusFilter) {
        return;
    }
    
    currentActiveFilter = activeFilter;
    currentStatusFilter = statusFilter;

    // Properly destroy existing table if it exists
    if ($.fn.DataTable.isDataTable('#bookingTable')) {
        $('#bookingTable').DataTable().destroy();
        $('#bookingTable').empty();
    }

    bookingTable = $('#bookingTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseUrl() + "/BookingsManagement/DataTables",
            type: "GET",
            data: function(d) {
                d.active_filter = activeFilter;
                d.status_filter = statusFilter;
            },
            error: function(xhr, error, thrown) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Loading Data',
                    text: 'Failed to load booking data. Please try refreshing the page.'
                });
            }
        },
        autoWidth: false,
        columns: [
            { 
                data: "booking_id",
                name: "b.booking_id",
                title: "ID",
                width: "10px",
            },
            {
                data: "customer_name",
                name: "customer_name",
                title: "Customer",
                width: "30px",
            },
            {
                data: "screening_id",
                name: "b.screening_id",
                title: "Screening ID",
                width: "30px",
            },
            {
                data: "seats",
                name: "b.seats",
                title: "Seats",
                width: "10px",
            },
            {
                data: "status",
                name: "b.status",
                title: "Status Type",
                width: "10px",
                render: function(data) {
                    let color;
                    switch(data?.toLowerCase()) {
                        case 'confirmed':
                            color = 'bg-green-500';
                            break;
                        case 'pending':
                            color = 'bg-yellow-500';
                            break;
                        case 'cancelled':
                            color = 'bg-red-500';
                            break;
                        default:
                            color = 'bg-gray-500';
                    }
                    return `<span class="px-2 py-1 ${color} text-white rounded-full text-sm">${data || 'Unknown'}</span>`;
                }
            },
            {
                data: "active",
                name: "b.active",
                title: "Active Status",
                width: "10px",
                render: function(data) {
                    return data === 'Active'
                        ? '<span class="px-2 py-1 bg-green-500 text-white rounded-full text-sm">Active</span>'
                        : '<span class="px-2 py-1 bg-red-500 text-white rounded-full text-sm">Inactive</span>';
                }
            },
            {
                data: "action",
                name: "action",
                title: "Actions",
                orderable: false,
                searchable: false,
                width: "10px",
            }
        ],
        order: [[0, "desc"]],
        drawCallback: function() {
            document.getElementById("active_filter").value = currentActiveFilter;
            document.getElementById("status_filter").value = currentStatusFilter;
            isInitialized = true;
        }
    });
}

// Initialize only once when document is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    if (!isInitialized) {
        initializeBookingTable();
    }

    // Handle active filter change
    document.getElementById("active_filter").addEventListener("change", function() {
        const statusFilter = document.getElementById("status_filter").value;
        initializeBookingTable(this.value, statusFilter);
    });

    // Handle status filter change
    document.getElementById("status_filter").addEventListener("change", function() {
        const activeFilter = document.getElementById("active_filter").value;
        initializeBookingTable(activeFilter, this.value);
    });
});

// Modal Functions
function openModal() {
    const modal = document.getElementById("addBookingModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    populateDropdowns();
}

function closeModal() {
    const modal = document.getElementById("addBookingModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

function openEditModal() {
    const modal = document.getElementById("editBookingModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    populateEditDropdowns();
}

function closeEditModal() {
    const modal = document.getElementById("editBookingModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

async function populateDropdowns() {
    try {
        // Fetch active customers
        const customersResponse = await fetch(baseUrl() + "/CustomersManagement/DataTables");
        const customersData = await customersResponse.json();
        const customerSelect = document.getElementById("customer_select");

        // Clear existing options
        customerSelect.innerHTML = '<option value="">Select Customer</option>';

        // Filter for active customers only
        const activeCustomers = customersData.data.filter(customer => customer.active === 1);

        activeCustomers.forEach(customer => {
            const option = document.createElement("option");
            option.value = customer.customer_id;
            option.textContent = `${customer.first_name} ${customer.last_name}`;
            customerSelect.appendChild(option);
        });

        // Fetch active screenings
        const screeningsResponse = await fetch(baseUrl() + "/ScreeningsManagement/DataTables");
        const screeningsData = await screeningsResponse.json();
        const screeningSelect = document.getElementById("screening_select");

        // Clear existing options
        screeningSelect.innerHTML = '<option value="">Select Screening</option>';

        // Filter for active screenings only
        const activeScreenings = screeningsData.data.filter(screening => screening.active === 1);

        activeScreenings.forEach(screening => {
            const option = document.createElement("option");
            option.value = screening.screening_id;
            const screeningTime = new Date(screening.screening_time).toLocaleString();
            option.textContent = `${screening.cinema_name} - ${screening.movie_title} (${screeningTime})`;
            screeningSelect.appendChild(option);
        });

    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to load dropdown data"
        });
    }
}

// Populate edit form dropdowns
async function populateEditDropdowns() {
    try {
        // Get the select elements
        const customerSelect = document.getElementById("edit_customer_select");
        const screeningSelect = document.getElementById("edit_screening_select");
        
        if (!customerSelect || !screeningSelect) {
            throw new Error('Select elements not found');
        }

        // Clear existing options
        customerSelect.innerHTML = '<option value="">Select Customer</option>';
        screeningSelect.innerHTML = '<option value="">Select Screening</option>';

        // Fetch and populate customers
        const customersResponse = await fetch(baseUrl() + "/CustomersManagement/DataTables");
        if (!customersResponse.ok) {
            throw new Error(`Customers fetch failed: ${customersResponse.status}`);
        }
        const customersData = await customersResponse.json();
        
        if (!customersData.data || !Array.isArray(customersData.data)) {
            throw new Error('Invalid customers data format');
        }

        // Add active customers
        const activeCustomers = customersData.data.filter(customer => 
            customer && customer.active === 1 && customer.customer_id
        );

        activeCustomers.forEach(customer => {
            const option = document.createElement("option");
            option.value = customer.customer_id;
            option.textContent = `${customer.first_name} ${customer.last_name}`;
            customerSelect.appendChild(option);
        });

        // Fetch and populate screenings
        const screeningsResponse = await fetch(baseUrl() + "/ScreeningsManagement/DataTables");
        if (!screeningsResponse.ok) {
            throw new Error(`Screenings fetch failed: ${screeningsResponse.status}`);
        }
        const screeningsData = await screeningsResponse.json();
        
        if (!screeningsData.data || !Array.isArray(screeningsData.data)) {
            throw new Error('Invalid screenings data format');
        }

        // Add active screenings
        const activeScreenings = screeningsData.data.filter(screening => 
            screening && 
            screening.active === 1 && 
            screening.screening_id &&
            screening.cinema_name &&
            screening.movie_title &&
            screening.screening_time
        );

        activeScreenings.forEach(screening => {
            const option = document.createElement("option");
            option.value = screening.screening_id;
            const screeningTime = new Date(screening.screening_time).toLocaleString();
            option.textContent = `${screening.cinema_name} - ${screening.movie_title} (${screeningTime})`;
            screeningSelect.appendChild(option);
        });

    } catch (error) {
        throw error;
    }
}

// Handle delete booking
$(document).on('click', '.delete-booking', function() {
    const bookingId = $(this).attr('data-id');
    if (!bookingId) {
        return;
    }
    
    Swal.fire({
        title: 'Are you sure?',
        text: "This booking will be deactivated!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, deactivate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            updateBookingStatus(bookingId);
        }
    });
});

// Function to update booking status
async function updateBookingStatus(bookingId) {
    if (!bookingId) {
        return;
    }

    try {
        const response = await fetch(`${baseUrl()}/BookingsManagement/updateStatus/${bookingId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Deactivated!',
                text: data.message || 'Booking has been deactivated successfully.',
                showConfirmButton: true
            });
            
            if (bookingTable) {
                bookingTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message || 'Failed to deactivate booking');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Status Update Failed',
            text: error.message || 'Failed to update booking status. Please try again.',
            showConfirmButton: true
        });
    }
}

// Initialize form submission
document.addEventListener("DOMContentLoaded", function() {
    // Initialize the table
    initializeBookingTable();

    // Add Booking Form Submission
    document.getElementById("addBookingForm")?.addEventListener("submit", async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch(baseUrl() + "/BookingsManagement/create", {
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
                    icon: "success",
                    title: "Success",
                    text: data.message || "Booking added successfully.",
                });

                this.reset();
                closeModal();
                
                if (bookingTable) {
                    bookingTable.ajax.reload(null, false);
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: data.message || "Failed to add Booking.",
                });
            }
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Unexpected Error",
                text: "Something went wrong. Please try again.",
            });
        }
    });

    // Edit Booking Form Submission
    document.getElementById("editBookingForm")?.addEventListener("submit", async function(e) {
        e.preventDefault();
        
        // Get form values directly
        const bookingId = document.getElementById('edit_booking_id').value;
        const customerId = document.getElementById('edit_customer_select').value;
        const screeningId = document.getElementById('edit_screening_select').value;
        const seatsValue = document.getElementById('edit_seats').value;
        const statusValue = document.getElementById('edit_status').value;
        
        // Validate required fields
        if (!bookingId || !customerId || !screeningId || !seatsValue || !statusValue) {
            Swal.fire({
                icon: "error",
                title: "Missing Fields",
                text: "Please fill in all required fields"
            });
            return;
        }
        
        // Create request data
        const requestData = {
            customer_id: customerId,
            screening_id: screeningId,
            set_number: seatsValue,
            status: statusValue
        };
        
        try {
            const response = await fetch(baseUrl() + `/BookingsManagement/update/${bookingId}`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(requestData)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Failed to update booking");
            }

            if (data.success) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: data.message || "Booking updated successfully.",
                });

                closeEditModal();

                if (bookingTable) {
                    bookingTable.ajax.reload(null, false);
                }
            } else {
                throw new Error(data.message || "Failed to update booking");
            }
        } catch (error) {
            console.error('Update error:', error);
            Swal.fire({
                icon: "error",
                title: "Update Failed",
                text: error.message || "Failed to update booking. Please try again.",
            });
        }
    });
});

// Handle edit booking
$(document).on('click', '.edit-booking', async function() {
    const bookingId = $(this).attr('data-id');
    if (!bookingId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Booking ID not found'
        });
        return;
    }

    const row = bookingTable.row($(this).closest('tr')).data();
    if (!row) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Could not retrieve booking data'
        });
        return;
    }

    // Open modal first
    openEditModal();

    try {
        // Wait for dropdowns to be populated
        await populateEditDropdowns();

        // Get form elements after modal is open and dropdowns are populated
        const customerSelect = document.getElementById('edit_customer_select');
        const screeningSelect = document.getElementById('edit_screening_select');
        const seatsInput = document.getElementById('edit_seats');
        const bookingIdInput = document.getElementById('edit_booking_id');
        const statusSelect = document.getElementById('edit_status');

        if (!customerSelect || !screeningSelect || !seatsInput || !bookingIdInput || !statusSelect) {
            throw new Error('Required form elements not found');
        }

        // Set the basic values
        bookingIdInput.value = bookingId;
        seatsInput.value = row.seats || '';
        statusSelect.value = row.status || 'confirmed';

        // Set customer and screening values
        let customerFound = false;
        let screeningFound = false;

        // First try setting by ID
        if (row.customer_id) {
            customerSelect.value = row.customer_id;
            customerFound = !!customerSelect.value;
        }

        if (row.screening_id) {
            screeningSelect.value = row.screening_id;
            screeningFound = !!screeningSelect.value;
        }

        // If ID setting failed, try matching by text
        if (!customerFound) {
            Array.from(customerSelect.options).forEach(option => {
                if (option.textContent.trim() === row.customer_name?.trim()) {
                    option.selected = true;
                    customerFound = true;
                }
            });
        }

        if (!screeningFound) {
            const rowScreeningTime = row.screening_time ? new Date(row.screening_time).toLocaleString() : '';
            Array.from(screeningSelect.options).forEach(option => {
                const screeningText = `${row.cinema_name} - ${row.movie_title} (${rowScreeningTime})`;
                if (option.textContent.trim() === screeningText.trim()) {
                    option.selected = true;
                    screeningFound = true;
                }
            });
        }

        // Show warning if any value couldn't be set
        if (!customerFound || !screeningFound) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Some booking data could not be loaded. Please verify the form data.',
                showConfirmButton: true
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load booking data: ' + error.message
        });
    }
});

// Handle restore booking
$(document).on('click', '.restore-booking', function() {
    const bookingId = $(this).attr('data-id');
    if (!bookingId) {
        return;
    }
    
    Swal.fire({
        title: 'Restore Booking?',
        text: "This will reactivate the booking!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, restore it!'
    }).then((result) => {
        if (result.isConfirmed) {
            restoreBookingStatus(bookingId);
        }
    });
});

// Function to restore booking status
async function restoreBookingStatus(bookingId) {
    if (!bookingId) {
        return;
    }

    try {
        const response = await fetch(`${baseUrl()}/BookingsManagement/restore/${bookingId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `Failed to restore booking: ${response.statusText}`);
        }

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Restored!',
                text: data.message || 'Booking has been restored successfully.',
                showConfirmButton: true
            });
            
            if (bookingTable) {
                bookingTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message || 'Failed to restore booking');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Restore Failed',
            text: error.message || 'Failed to restore booking. Please try again.',
            showConfirmButton: true
        });
    }
}