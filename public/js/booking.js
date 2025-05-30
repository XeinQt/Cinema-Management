let bookingTable;

// Initialize dropdownCache at global scope
const dropdownCache = {
    cinemas: null,
    movies: null
};

function initializeBookingTable() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#bookingTable')) {
        $('#bookingTable').DataTable().destroy();
    }

    bookingTable = $('#bookingTable').DataTable({
        ajax: {
            url: baseUrl() + '/BookingsManagement/DataTables',
            type: 'GET',
            data: function(d) {
                d.status_filter = $('#status_filter').val();
                d.filter = $('#filter').val() === 'active' ? '1' : ($('#filter').val() === 'inactive' ? '0' : '');
            },
            error: function(xhr, error, thrown) {
                Swal.fire({
                    icon: 'error',
                    title: 'Loading Error',
                    text: 'Failed to load booking data. Please try refreshing the page.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Refresh Page',
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            }
        },
        processing: true,
        serverSide: true,
        scrollX: true,
        columns: [
            { 
                data: 'booking_id', 
                name: 'booking_id', 
                title: 'ID',
                className: 'font-semibold'
            },
            { 
                data: 'customer_name', 
                name: 'customer_name', 
                title: 'Customer',
                render: function(data) {
                    return `<div class="font-medium">${data}</div>`;
                }
            },
            { data: 'cinema_name', name: 'cinema_name', title: 'Cinema' },
            { data: 'movie_title', name: 'movie_title', title: 'Movie' },
            { 
                data: 'screening_time', 
                name: 'screening_time', 
                title: 'Time',
                render: function(data) {
                    const date = new Date(data);
                    return `<div class="whitespace-nowrap">${date.toLocaleString('en-US', {
                        weekday: 'short',
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</div>`;
                }
            },
            { 
                data: 'set_number', 
                name: 'set_number', 
                title: 'Seat',
                className: 'font-medium text-center',
                render: function(data) {
                    return `<div class="text-center">${data}</div>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                title: 'Status',
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    let colorClass = '';
                    let icon = '';
                    switch(data.toLowerCase()) {
                        case 'confirmed':
                            colorClass = 'bg-green-500';
                            icon = '✓';
                            break;
                        case 'cancelled':
                            colorClass = 'bg-red-500';
                            icon = '✕';
                            break;
                        default:
                            colorClass = 'bg-gray-500';
                            icon = '?';
                    }
                    return `<div class="flex justify-center gap-2">
                        <span class="px-3 py-1 text-white text-sm rounded-full ${colorClass}">
                            ${icon} ${data}
                        </span>
                    </div>`;
                }
            },
            {
                data: 'active',
                name: 'active',
                title: 'Active Status',
                className: 'text-center align-middle',
                render: function(data) {
                    return `<div class="flex justify-center gap-2">
                        ${data === 1 ? 
                            '<span class="px-3 py-1 text-white text-sm rounded-full bg-green-500">✓ Active</span>' : 
                            '<span class="px-3 py-1 text-white text-sm rounded-full bg-red-500">✕ Inactive</span>'
                        }
                    </div>`;
                }
            },
            {
                data: null,
                title: 'Actions',
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    if (row.active == 1) {
                        return `
                            <div style="text-align: center;">
                                <button class="edit-booking bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg transition-colors duration-200 mx-1" data-id="${row.booking_id}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="delete-booking bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition-colors duration-200 mx-1" data-id="${row.booking_id}">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </div>
                        `;
                    } else {
                        return `
                            <div style="text-align: center;">
                                <button class="edit-booking bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg transition-colors duration-200 mx-1" data-id="${row.booking_id}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="restore-booking bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg transition-colors duration-200 mx-1" data-id="${row.booking_id}">
                                    <i class="fas fa-undo"></i> Restore
                                </button>
                            </div>
                        `;
                    }
                }
            }
        ],
        columnDefs: [
            {
                targets: '_all',
                className: 'px-6 py-4 text-gray-900'
            },
            {
                targets: [0, 5],  // ID and Seat columns
                className: 'px-6 py-4 text-gray-900 text-start'
            },
            {
                targets: [7, 8],  // Status, Active Status, and Actions columns
                className: 'px-6 py-4 text-gray-900 text-center align-middle ml-[38px]'
            }
        ],
        dom: '<"top flex flex-col sm:flex-row justify-between items-center mb-4 gap-4"<"flex flex-col sm:flex-row items-center gap-4"l<"custom-filters">>f>rt<"bottom"ip><"clear">',
        createdRow: function(row, data, dataIndex) {
            $(row).addClass('hover:bg-gray-50 transition-colors duration-150');
        },
        initComplete: function() {
            // Remove any existing filters first
            $('.custom-filters').empty();
            
            // Create filter container
            const filterContainer = $(`
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <label for="status_filter" class="text-gray-700">Status:</label>
                        <select id="status_filter" class="px-3 py-2 border rounded-lg bg-white text-gray-900">
                            <option value="">All Status</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label for="filter" class="text-gray-700">Filter:</label>
                        <select id="filter" class="px-3 py-2 border rounded-lg bg-white text-gray-900">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            `).appendTo('.custom-filters');

            // Add filter change handlers
            $('#status_filter, #filter').on('change', function() {
                bookingTable.ajax.reload();
            });
        }
    });

    // Add custom styling
    $('head').append(`
        <style>
            .dataTables_wrapper {
                padding: 1rem;
                background: white;
                border-radius: 0.5rem;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            }
            
            .dataTables_wrapper .dataTables_length select {
                padding: 0.5rem 2rem 0.5rem 1rem;
                border-radius: 0.375rem;
                border-color: #e2e8f0;
                background-color: white;
            }
            
            .dataTables_wrapper .dataTables_filter input {
                padding: 0.5rem 1rem;
                border-radius: 0.375rem;
                border: 1px solid #e2e8f0;
                margin-left: 0.5rem;
                width: 250px;
            }
            
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.5rem 1rem;
                margin: 0 2px;
                border-radius: 0.375rem;
                border: 1px solid #e2e8f0;
                background: white;
                color: #4a5568 !important;
            }
            .dataTables_wrapper .dataTables_info {
                padding: 1rem 0;
                color: #4a5568;
            }
            
            #bookingTable {
                width: 100% !important;
                border-collapse: separate;
                border-spacing: 0;
            }
            
            #bookingTable thead th {
                background-color: #f7fafc;
                padding: 1rem 0.75rem;
                font-weight: 600;
                text-align: left;
                border-bottom: 2px solid #e2e8f0;
                white-space: nowrap;
            }
            
            #bookingTable tbody td {
                padding: 1rem 0.75rem;
                border-bottom: 1px solid #e2e8f0;
                vertical-align: middle;
            }
            
            .dataTables_scrollBody {
                min-height: 400px;
                border: 1px solid #e2e8f0;
                border-radius: 0.5rem;
            }
            
            .dataTables_processing {
                background: rgba(255,255,255,0.9) !important;
                color: #2d3748;
                border: 1px solid #e2e8f0;
                border-radius: 0.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                padding: 1rem !important;
                z-index: 100;
            }

            @media (max-width: 640px) {
                .dataTables_wrapper .dataTables_filter input {
                    width: 100%;
                    margin-left: 0;
                }
                
                .dataTables_wrapper .dataTables_length,
                .dataTables_wrapper .dataTables_filter {
                    text-align: left;
                    width: 100%;
                }
            }

            /* Status columns specific styles */
            #bookingTable td:nth-child(7),
            #bookingTable th:nth-child(7),
            #bookingTable td:nth-child(8),
            #bookingTable th:nth-child(8) {
                text-align: center !important;
                vertical-align: middle !important;
            }
            
            /* Ensure consistent flex centering for status badges */
            #bookingTable td:nth-child(7) > div,
            #bookingTable td:nth-child(8) > div {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
            }
            
            /* Status badge styling */
            #bookingTable td:nth-child(7) span,
            #bookingTable td:nth-child(8) span {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 90px;
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
    initializeBookingTable();

    // Add event listener for the Add button
    document.querySelector('button[onclick="openModal()"]')?.addEventListener('click', () => {
        populateDropdowns();
    });

    // Add event listeners for movie and cinema selection
    document.getElementById('cinema_select')?.addEventListener('change', fetchScreeningTimes);
    document.getElementById('movie_select')?.addEventListener('change', fetchScreeningTimes);

    // Add event listeners for edit form movie and cinema selection
    document.getElementById('edit_cinema_select')?.addEventListener('change', fetchEditScreeningTimes);
    document.getElementById('edit_movie_select')?.addEventListener('change', fetchEditScreeningTimes);

    // Add booking form submission handler
    document.getElementById("addBookingForm")?.addEventListener("submit", async function(e) {
        e.preventDefault();

        const screeningSelect = document.getElementById('screening_time');
        const selectedScreening = screeningSelect.options[screeningSelect.selectedIndex];
        const customerName = document.getElementById('customer_full_name').value;
        const seatNumber = document.getElementById('seat_number').value;

        // Validate required fields
        if (!screeningSelect.value || !customerName || !seatNumber) {
            let missingFields = [];
            if (!customerName) missingFields.push('Customer Name');
            if (!screeningSelect.value) missingFields.push('Screening Time');
            if (!seatNumber) missingFields.push('Seat Number');

            Swal.fire({
                icon: "warning",
                title: "Missing Information",
                text: `Please fill in: ${missingFields.join(', ')}`,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'I\'ll Complete It'
            });
            return;
        }

        // Get selected options text for confirmation
        const cinemaName = document.getElementById('cinema_select').options[document.getElementById('cinema_select').selectedIndex].text;
        const movieTitle = document.getElementById('movie_select').options[document.getElementById('movie_select').selectedIndex].text;
        const screeningTime = selectedScreening.text;

        // Show confirmation dialog
        const result = await Swal.fire({
            title: 'Confirm Your Booking',
            text: `Customer: ${customerName}\nMovie: ${movieTitle}\nCinema: ${cinemaName}\nTime: ${screeningTime}\nSeat: ${seatNumber}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Confirm Booking',
            cancelButtonText: 'Make Changes'
        });

        if (!result.isConfirmed) {
            return;
        }

        // Show loading state
        Swal.fire({
            title: 'Processing Your Booking',
            text: 'Please wait while we confirm your booking...',
            allowOutsideClick: false,
            showConfirmButton: false
        });

        const formData = new FormData(this);
        formData.append('screening_id', screeningSelect.value);
        formData.append('cinema_id', selectedScreening.dataset.cinemaId);
        formData.append('movie_id', selectedScreening.dataset.movieId);
        formData.append('screening_time', selectedScreening.dataset.time);

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
                    title: "Booking Confirmed! 🎉",
                    text: `Booking successful`,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Done'
                });

                this.reset();
                closeModal();
                clearDropdownCache();
                
                // Refresh the table after adding a booking
                if (bookingTable) {
                    bookingTable.ajax.reload(null, false);
                }
            } else {
                let errorMessage = data.message || "Failed to add booking.";
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join(', ');
                }
                Swal.fire({
                    icon: "error",
                    title: "Booking Failed",
                    text: errorMessage,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Try Again'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "System Error",
                text: error.message || 'Could not process your booking. Please try again or contact support.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Try Again',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                cancelButtonColor: '#d33'
            });
        }
    });
});

// Function to populate cinema and movie dropdowns
async function populateDropdowns() {
    try {
        const cinemaSelect = document.getElementById('cinema_select');
        const movieSelect = document.getElementById('movie_select');
        const timeSelect = document.getElementById('screening_time');
        
        if (!cinemaSelect || !movieSelect) {
            console.error('Cinema or Movie select elements not found');
            return;
        }

        // Reset all dropdowns
        cinemaSelect.innerHTML = '<option value="">Select Cinema</option>';
        movieSelect.innerHTML = '<option value="">Select Movie</option>';
        timeSelect.innerHTML = '<option value="">Select Time</option>';
        timeSelect.disabled = true;

        try {
            const [cinemasResponse, moviesResponse] = await Promise.all([
                fetch(baseUrl() + '/CinemasManagement/DataTables'),
                fetch(baseUrl() + '/MoviesManagement/DataTables')
            ]);

            if (!cinemasResponse.ok || !moviesResponse.ok) {
                throw new Error('Failed to fetch data');
            }

            const [cinemasData, moviesData] = await Promise.all([
                cinemasResponse.json(),
                moviesResponse.json()
            ]);

            // Cache the responses
            dropdownCache.cinemas = cinemasData;
            dropdownCache.movies = moviesData;

            // Filter only active cinemas
            const activeCinemas = cinemasData.data.filter(cinema => cinema.active === 1);
            activeCinemas.forEach(cinema => {
                const option = new Option(cinema.name, cinema.cinema_id);
                cinemaSelect.appendChild(option);
            });

            // Filter only active movies
            const activeMovies = moviesData.data.filter(movie => movie.active === 1);
            activeMovies.forEach(movie => {
                const option = new Option(movie.title, movie.movie_id);
                movieSelect.appendChild(option);
            });

        } catch (error) {
            console.error('Error fetching data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load cinema and movie data'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to initialize dropdowns'
        });
    }
}

// Function to clear dropdown cache
function clearDropdownCache() {
    dropdownCache.cinemas = null;
    dropdownCache.movies = null;
}

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
        const response = await fetch(baseUrl() + '/ScreeningsManagement/DataTables');
        if (!response.ok) {
            throw new Error('Failed to fetch screening times');
        }

        const data = await response.json();
        console.log('All screenings:', data.data); // Debug log
        
        // Filter screenings for selected cinema and movie
        const availableScreenings = data.data.filter(screening => {
            console.log('Checking screening:', screening); // Debug log
            console.log('Comparing:', {
                cinema: screening.cinema_id == cinemaId,
                movie: screening.movie_id == movieId
            });
            return screening.cinema_id == cinemaId && 
                   screening.movie_id == movieId;
        });

        console.log('Available screenings:', availableScreenings); // Debug log

        // Clear and populate time dropdown
        timeSelect.innerHTML = '<option value="">Select screening time</option>';
        
        if (availableScreenings.length === 0) {
            timeSelect.innerHTML = '<option value="" disabled>No screenings available</option>';
            timeSelect.disabled = true;
            
            const cinemaName = document.getElementById('cinema_select').options[document.getElementById('cinema_select').selectedIndex].text;
            const movieTitle = document.getElementById('movie_select').options[document.getElementById('movie_select').selectedIndex].text;
            
            Swal.fire({
                icon: 'info',
                title: 'No Screenings Available',
                text: `No screenings found. Please try selecting a different movie or cinema.`,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Enable the select and add screenings
        timeSelect.disabled = false;
        availableScreenings.forEach(screening => {
            try {
                const screeningTime = new Date(screening.screening_time);
                console.log('Processing screening time:', screeningTime); // Debug log
                
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
                option.dataset.cinemaId = screening.cinema_id;
                option.dataset.movieId = screening.movie_id;
                option.dataset.time = screening.screening_time;
                timeSelect.appendChild(option);
                
                console.log('Added screening option:', {
                    id: screening.screening_id,
                    time: formattedTime,
                    originalTime: screening.screening_time
                }); // Debug log
            } catch (timeError) {
                console.error('Error processing screening time:', timeError);
            }
        });

        console.log('Screenings populated:', availableScreenings.length, 'available times');

    } catch (error) {
        console.error('Error fetching screening times:', error);
        timeSelect.innerHTML = '<option value="">Error loading screening times</option>';
        timeSelect.disabled = true;
        
        Swal.fire({
            icon: 'error',
            title: 'Error Loading Screenings',
            text: 'Failed to load screening times. Please try again or contact support if the problem persists.',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Try Again'
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

// Add restore booking handler
$(document).on('click', '.restore-booking', function() {
    const bookingId = $(this).data('id');
    
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
            restoreBooking(bookingId);
        }
    });
});

// Function to restore booking
async function restoreBooking(bookingId) {
    try {
        const response = await fetch(`${baseUrl()}/BookingsManagement/restore/${bookingId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire(
                'Restored!',
                'Booking has been restored successfully.',
                'success'
            );
            // Reload the DataTable to reflect the changes
            if (bookingTable) {
                bookingTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message || 'Failed to restore booking');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            error.message || 'Failed to restore booking',
            'error'
        );
    }
}