let movieTable;

function initializeMovieTable() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#movieTable')) {
        $('#movieTable').DataTable().destroy();
    }

    movieTable = $('#movieTable').DataTable({
        ajax: {
            url: baseUrl() + '/MoviesManagement/DataTables',
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load movie data. Please try again.'
                });
            }
        },
        processing: true,
        serverSide: true,
        scrollX: true,
        columns: [
            { data: 'movie_id', name: 'movie_id', title: 'ID' },
            { data: 'title', name: 'title', title: 'Title' },
            { data: 'genre', name: 'genre', title: 'Genre' },
            { data: 'duration', name: 'duration', title: 'Duration' },
            { data: 'description', name: 'description', title: 'Description' },
            { data: 'rating', name: 'rating', title: 'Rating' },
            { 
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    return getActionButtons(row.movie_id, 'movie');
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
                targets: 1, // Title column
                width: '200px'
            },
            {
                targets: 2, // Genre column
                width: '120px'
            },
            {
                targets: 3, // Duration column
                width: '100px',
                className: 'text-center'
            },
            {
                targets: 4, // Description column
                width: '300px',
                render: function(data, type, row) {
                    if (type === 'display' && data) {
                        return '<div class="description-cell" title="' + data + '">' + 
                               (data.length > 100 ? data.substr(0, 100) + '...' : data) + 
                               '</div>';
                    }
                    return data;
                }
            },
            {
                targets: 5, // Rating column
                width: '80px',
                className: 'text-center'
            },
            {
                targets: -1, // Actions column (last)
                width: '150px',
                className: 'text-center',
                orderable: false,
                searchable: false
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
        },
        drawCallback: function() {
            // Add tooltips to description cells
            $('.description-cell').each(function() {
                if (this.scrollWidth > this.offsetWidth) {
                    $(this).attr('title', $(this).text());
                }
            });
        },
        initComplete: function() {
            console.log('DataTable initialized successfully');
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
            #movieTable {
                width: 100% !important;
            }
            #movieTable th, #movieTable td {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .description-cell {
                max-width: 300px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            #movieTable .btn {
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

// Handle delete movie
$(document).on('click', '.delete-movie', function() {
    const movieId = $(this).data('id');
    
    Swal.fire({
        title: 'Are you sure?',
        text: "This movie will be deactivated!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, deactivate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            updateMovieStatus(movieId);
        }
    });
});

// Function to update movie status
async function updateMovieStatus(movieId) {
    try {
        const response = await fetch(`${baseUrl()}/MoviesManagement/updateStatus/${movieId}`, {
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
            if (movieTable) {
                movieTable.ajax.reload(null, false);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            error.message || 'Failed to update movie status',
            'error'
        );
    }
}

// Modal Functions
function openModal() {
    const modal = document.getElementById("addMovieModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeModal() {
    const modal = document.getElementById("addMovieModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

// Initialize form submission
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById("addMovieForm")?.addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch(baseUrl() + "/MoviesManagement/create", {
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
                    text: data.message || 'Movie added successfully.'
                });

                this.reset();
                closeModal();
                
                // Refresh the table after adding a movie
                if (movieTable) {
                    movieTable.ajax.reload(null, false);
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: data.message || "Failed to add Movie.",
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


//kuhaon ang value sa modal
function openEditModal() {
    const modal = document.getElementById("editMovieModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeEditModal() {
    const modal = document.getElementById("editMovieModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
}

$(document).on('click', '.edit-movie', function() {
    const movieId = $(this).data('id');
    const row = movieTable.row($(this).closest('tr')).data();
    
    // Populate the edit form
    document.getElementById('edit_movie_id').value = movieId;
    document.getElementById('edit_title').value = row.title;
    document.getElementById('edit_genre').value = row.genre;
    document.getElementById('edit_duration').value = row.duration;
    document.getElementById('edit_description').value = row.description;
    document.getElementById('edit_rating').value = row.rating;
    
    // Open the edit modal
    openEditModal();
});

// Edit Movie Form
document.getElementById("editMovieForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const movieId = formData.get('movie_id');

    try {
        const response = await fetch(`${baseUrl()}/MoviesManagement/update/${movieId}`, {
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
                text: data.message || 'Movie updated successfully.'
            });

            closeEditModal();
            
            if (movieTable) {
                movieTable.ajax.reload(null, false);
            }
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: data.message || "Failed to update Movie.",
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