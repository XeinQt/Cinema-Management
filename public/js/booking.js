let bookingTable;

function initializeBookingTable() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#bookingTable')) {
        $('#bookingTable').DataTable().destroy();
    }

    bookingTable = $('#bookingTable').DataTable({
        ajax: {
            url: baseUrl() + '/BookingsManagement/DataTables',
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load booking data. Please try again.'
                });
            }
        },
        processing: true,
        serverSide: true,
        scrollX: true,
        columns: [
            { data: 'booking_id', name: 'booking_id', title: 'ID' },
            { data: 'customer_name', name: 'customer_name', title: 'Customer' },
            { data: 'cinema_name', name: 'cinema_name', title: 'Cinema' },
            { data: 'movie_title', name: 'movie_title', title: 'Movie' },
            { data: 'screening_time', name: 'screening_time', title: 'Time',
                render: function(data) {
                    const date = new Date(data);
                    return date.toLocaleString();
                }
            },
            { data: 'set_number', name: 'set_number', title: 'Seat' },
            {
                data: 'status',
                name: 'status',
                title: 'Status',
                render: function(data, type, row) {
                    let colorClass = '';
                    switch(data.toLowerCase()) {
                        case 'confirmed':
                            colorClass = 'bg-green-500';
                            break;
                        case 'cancelled':
                            colorClass = 'bg-red-500';
                            break;
                        default:
                            colorClass = 'bg-yellow-500';
                    }
                    return `<span class="px-2 py-1 text-white text-sm rounded-full ${colorClass}">${data}</span>`;
                }
            },
            {
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    return getActionButtons(row.booking_id, 'booking');
                }
            }
        ],
        columnDefs: [
            {
                targets: 0, // ID column
                width: '50px',
                className: 'text-center'
            },
            {
                targets: [1, 2, 3], // Customer, Cinema, Movie columns
                width: '150px'
            },
            {
                targets: 4, // Time column
                width: '180px',
                className: 'text-center'
            },
            {
                targets: 5, // Seat column
                width: '80px',
                className: 'text-center'
            },
            {
                targets: 6, // Status column
                width: '100px',
                className: 'text-center'
            },
            {
                targets: -1, // Actions column
                width: '150px',
                className: 'text-center'
            }
        ],
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        autoWidth: false,
        responsive: true,
        pageLength: 10,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
            processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
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
            #bookingTable {
                width: 100% !important;
            }
            #bookingTable th, #bookingTable td {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            #bookingTable .btn {
                padding: 0.25rem 0.5rem;
                margin: 0 2px;
            }
            .dataTables_scrollBody {
                min-height: 400px;
            }
            .spinner-border {
                display: inline-block;
                width: 2rem;
                height: 2rem;
                vertical-align: text-bottom;
                border: 0.25em solid currentColor;
                border-right-color: transparent;
                border-radius: 50%;
                animation: spinner-border .75s linear infinite;
            }
            @keyframes spinner-border {
                to { transform: rotate(360deg); }
            }
        </style>
    `);
}

// Handle delete booking
$(document).on('click', '.delete-booking', function() {
    const bookingId = $(this).data('id');
    
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
    try {
        const response = await fetch(`${baseUrl()}/BookingsManagement/updateStatus/${bookingId}`, {
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
            if (bookingTable) {
                bookingTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            error.message || 'Failed to update booking status',
            'error'
        );
    }
}

// Modal Functions
function openModal() {
    const modal = document.getElementById("addBookingModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeModal() {
    const modal = document.getElementById("addBookingModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// Initialize form submission
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById("addBooking")?.addEventListener("submit", async function (e) {
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
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Booking added successfully.'
                });

                this.reset();
                closeModal();
                
                // Refresh the table after adding a booking
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
            console.error("Fetch Error:", error);
            Swal.fire({
                icon: "error",
                title: "Unexpected Error",
                text: "Something went wrong. Please try again.",
            });
        }
    });
});

// Function to populate cinema and movie dropdowns
async function populateDropdowns() {
    try {
        // Fetch cinemas
        const cinemasResponse = await fetch('/CinemasManagement/DataTables');
        const cinemasData = await cinemasResponse.json();
        const cinemaSelect = document.getElementById('cinema_select');
        
        cinemasData.data.forEach(cinema => {
            const option = document.createElement('option');
            option.value = cinema.cinema_id;
            option.textContent = cinema.name;
            cinemaSelect.appendChild(option);
        });

        // Fetch movies
        const moviesResponse = await fetch('/MoviesManagement/DataTables');
        const moviesData = await moviesResponse.json();
        const movieSelect = document.getElementById('movie_select');
        
        moviesData.data.forEach(movie => {
            const option = document.createElement('option');
            option.value = movie.movie_id;
            option.textContent = movie.title;
            movieSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading dropdowns:', error);
    }
}

// Call populateDropdowns when opening the modal
document.querySelector('button.bg-green-500').addEventListener('click', () => {
    openModal();
    populateDropdowns();
});

// Add event listeners for movie and cinema selection
document.getElementById('cinema_select').addEventListener('change', fetchScreeningTimes);
document.getElementById('movie_select').addEventListener('change', fetchScreeningTimes);

// Function to fetch and populate screening times
async function fetchScreeningTimes() {
    const cinemaId = document.getElementById('cinema_select').value;
    const movieId = document.getElementById('movie_select').value;
    const timeSelect = document.getElementById('screening_time');

    // Reset and disable time dropdown if either cinema or movie is not selected
    if (!cinemaId || !movieId) {
        timeSelect.innerHTML = '<option value="">Select movie and cinema first</option>';
        timeSelect.disabled = true;
        return;
    }

    try {
        const response = await fetch('/ScreeningsManagement/DataTables');
        const data = await response.json();
        
        // Filter screenings for selected cinema and movie
        const availableScreenings = data.data.filter(screening => 
            screening.cinema_id == cinemaId && 
            screening.movie_id == movieId
        );

        // Clear and populate time dropdown
        timeSelect.innerHTML = '<option value="">Select screening time</option>';
        
        if (availableScreenings.length === 0) {
            timeSelect.innerHTML += '<option value="" disabled>No screenings available</option>';
        } else {
            availableScreenings.forEach(screening => {
                const screeningTime = new Date(screening.screening_time);
                const formattedTime = screeningTime.toLocaleString('en-US', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                timeSelect.innerHTML += `
                    <option value="${screening.screening_time}">
                        ${formattedTime}
                    </option>
                `;
            });
        }
        
        timeSelect.disabled = false;
    } catch (error) {
        console.error('Error fetching screening times:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load screening times'
        });
    }
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

// Function to populate edit form dropdowns
async function populateEditDropdowns() {
    try {
        const cinemaSelect = document.getElementById('edit_cinema_select');
        const movieSelect = document.getElementById('edit_movie_select');
        
        // Clear existing options except the first one
        while (cinemaSelect.options.length > 1) cinemaSelect.remove(1);
        while (movieSelect.options.length > 1) movieSelect.remove(1);

        const [cinemasResponse, moviesResponse] = await Promise.all([
            fetch('/CinemasManagement/DataTables'),
            fetch('/MoviesManagement/DataTables')
        ]);

        const [cinemasData, moviesData] = await Promise.all([
            cinemasResponse.json(),
            moviesResponse.json()
        ]);

        // Populate cinemas
        cinemasData.data.forEach(cinema => {
            const option = document.createElement('option');
            option.value = cinema.cinema_id;
            option.textContent = cinema.name;
            cinemaSelect.appendChild(option);
        });

        // Populate movies
        moviesData.data.forEach(movie => {
            const option = document.createElement('option');
            option.value = movie.movie_id;
            option.textContent = movie.title;
            movieSelect.appendChild(option);
        });

    } catch (error) {
        console.error('Error loading dropdowns:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load cinema and movie data'
        });
    }
}

// Add event listeners for edit form movie and cinema selection
document.getElementById('edit_cinema_select')?.addEventListener('change', fetchEditScreeningTimes);
document.getElementById('edit_movie_select')?.addEventListener('change', fetchEditScreeningTimes);

// Function to fetch and populate screening times for edit form
async function fetchEditScreeningTimes() {
    const cinemaId = document.getElementById('edit_cinema_select').value;
    const movieId = document.getElementById('edit_movie_select').value;
    const timeSelect = document.getElementById('edit_screening_time');

    // Reset and disable time dropdown if either cinema or movie is not selected
    if (!cinemaId || !movieId) {
        timeSelect.innerHTML = '<option value="">Select movie and cinema first</option>';
        timeSelect.disabled = true;
        return;
    }

    try {
        const response = await fetch('/ScreeningsManagement/DataTables');
        const data = await response.json();
        
        // Filter screenings for selected cinema and movie
        const availableScreenings = data.data.filter(screening => 
            screening.cinema_id == cinemaId && 
            screening.movie_id == movieId
        );

        // Clear and populate time dropdown
        timeSelect.innerHTML = '<option value="">Select screening time</option>';
        timeSelect.disabled = false;
        
        if (availableScreenings.length === 0) {
            timeSelect.innerHTML += '<option value="" disabled>No screenings available</option>';
        } else {
            availableScreenings.forEach(screening => {
                const screeningTime = new Date(screening.screening_time);
                const formattedTime = screeningTime.toLocaleString('en-US', {
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                const option = document.createElement('option');
                option.value = screening.screening_id;
                option.textContent = formattedTime;
                option.selected = screening.screening_id == timeSelect.getAttribute('data-selected-id');
                timeSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load screening times'
        });
    }
}

// Handle edit button click
$(document).on('click', '.edit-booking', async function() {
    const bookingId = $(this).data('id');
    const row = bookingTable.row($(this).closest('tr')).data();
    
    // Open modal first
    openEditModal();
    
    // Set basic form values
    document.getElementById('edit_booking_id').value = bookingId;
    document.getElementById('edit_customer_full_name').value = row.customer_name;
    document.getElementById('edit_seat_number').value = row.set_number;
    document.getElementById('edit_status').value = row.status.toLowerCase();
    
    // Wait for dropdowns to be populated
    await new Promise(resolve => {
        const checkDropdowns = setInterval(() => {
            const cinemaSelect = document.getElementById('edit_cinema_select');
            const movieSelect = document.getElementById('edit_movie_select');
            
            if (cinemaSelect.options.length > 1 && movieSelect.options.length > 1) {
                clearInterval(checkDropdowns);
                
                // Set cinema and movie values
                cinemaSelect.value = row.cinema_id;
                movieSelect.value = row.movie_id;
                
                // Now fetch screening times
                fetchEditScreeningTimes().then(() => {
                    setTimeout(() => {
                        const timeSelect = document.getElementById('edit_screening_time');
                        timeSelect.value = row.screening_id;
                        resolve();
                    }, 100); // Small delay to ensure screening times are populated
                });
            }
        }, 100);
    });
});

// Handle edit form submission
document.getElementById("editBookingForm")?.addEventListener("submit", async function(e) {
    e.preventDefault();
    
    const bookingId = document.getElementById('edit_booking_id').value;
    const formData = new FormData(this);
    
    try {
        const response = await fetch(`${baseUrl()}/BookingsManagement/update/${bookingId}`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json"
            },
            body: formData
        });

        const data = await response.json();

        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message || 'Booking updated successfully.'
            });

            closeEditModal();
            if (bookingTable) {
                bookingTable.ajax.reload(null, false);
            }
        } else {
            let errorMessage = data.message || "Failed to update Booking.";
            if (data.errors) {
                errorMessage = Object.values(data.errors).flat().join('\n');
            }
            Swal.fire({
                icon: "error",
                title: "Error",
                text: errorMessage
            });
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        Swal.fire({
            icon: "error",
            title: "Unexpected Error",
            text: "Network error or server is not responding. Please try again."
        });
    }
});