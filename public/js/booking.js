let bookingTable;

function initializeBookingTable() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#bookingTable')) {
        $('#bookingTable').DataTable().destroy();
    }

    bookingTable = $('#bookingTable').DataTable({
        ajax: baseUrl() + '/BookingsManagement/DataTables',
        processing: true,
        serverSide: true,
        columns: [
            { data: 'booking_id', name: 'booking_id', title: 'Booking ID' },
            { data: 'customer_id', name: 'customer_id', title: 'Customer ID' },
            { data: 'screening_id', name: 'screening_id', title: 'Screening ID' },
            { data: 'set_number', name: 'set_number', title: 'Set Number' },
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
        ]
    });
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