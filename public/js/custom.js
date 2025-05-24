function baseUrl() {
    return location.protocol + "//" + location.host + "";
}

// Common function for action buttons
function getActionButtons(id, type) {
    return `
        <div class="flex space-x-2">
            <button class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md edit-${type}" data-id="${id}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit
            </button>
            <button class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded-md delete-${type}" data-id="${id}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Delete
            </button>
        </div>`;
}

//malls table
new DataTable("#mallsDatatables", {
    ajax: baseUrl() + "/MallsManagement/DataTables",
    processing: true,
    serverSide: true,
    columns: [
        {
            data: "mall_id",
            name: "mall_id",
            title: "Mall ID"
        },
        {
            data: "name",
            name: "name",
            title: "Name"
        },
        {
            data: "location",
            name: "location",
            title: "Location"
        },
        {
            data: "description",
            name: "description",
            title: "Description"
        },
        {
            data: null,
            title: "Actions",
            orderable: false,
            render: function(data, type, row) {
                return getActionButtons(row.mall_id, 'mall');
            }
        }
    ]
});

//customer tables
new DataTable("#customerTable", {
    ajax: baseUrl() + "/CustomersManagement/DataTables",
    processing: true,
    serverSide: true,
    columns: [
        {
            data: "customer_id",
            name: "customer_id",
            title: "Customer ID"
        },
        {
            data: "first_name",
            name: "first_name",
            title: "First Name"
        },
        {
            data: "last_name",
            name: "last_name",
            title: "Last Name"
        },
        {
            data: "email",
            name: "email",
            title: "Email"
        },
        {
            data: "phonenumber",
            name: "phonenumber",
            title: "Phone No."
        },
        {
            data: null,
            title: "Actions",
            orderable: false,
            render: function(data, type, row) {
                return getActionButtons(row.customer_id, 'customer');
            }
        }
    ]
});

//cinemas
new DataTable("#cinemaTable", {
    ajax: baseUrl() + "/CinemasManagement/DataTables",
    processing: true,
    serverSide: true,
    columns: [
        {
            data: "cinema_id",
            name: "cinema_id",
            title: "Cinema ID"
        },
        {
            data: "mall_id",
            name: "mall_id",
            title: "Mall Id"
        },
        {
            data: "manager_id",
            name: "manager_id",
            title: "Manager Id"
        },
        {
            data: "name",
            name: "name",
            title: "Name"
        },
        {
            data: null,
            title: "Actions",
            orderable: false,
            render: function(data, type, row) {
                return getActionButtons(row.cinema_id, 'cinema');
            }
        }
    ]
});

//manager tables
new DataTable("#managerTable", {
    ajax: baseUrl() + "/ManagersManagement/DataTables",
    processing: true,
    serverSide: true,
    columns: [
        {
            data: "manager_id",
            name: "manager_id",
            title: "Manager ID"
        },
        {
            data: "first_name",
            name: "first_name",
            title: "First Name"
        },
        {
            data: "last_name",
            name: "last_name",
            title: "Last Name"
        },
        {
            data: "email",
            name: "email",
            title: "Email"
        },
        {
            data: "phonenumber",
            name: "phonenumber",
            title: "Phone No."
        },
        {
            data: null,
            title: "Actions",
            orderable: false,
            render: function(data, type, row) {
                return getActionButtons(row.manager_id, 'manager');
            }
        }
    ]
});

//movies tables
new DataTable("#moviesTable", {
    ajax: baseUrl() + "/MoviesManagement/DataTables",
    processing: true,
    serverSide: true,
    columns: [
        {
            data: "movie_id",
            name: "movie_id",
            title: "Movies ID"
        },
        {
            data: "title",
            name: "title",
            title: "Title"
        },
        {
            data: "genre",
            name: "genre",
            title: "Genre"
        },
        {
            data: "duration",
            name: "duration",
            title: "Duration"
        },
        {
            data: "description",
            name: "description",
            title: "Description"
        },
        {
            data: "rating",
            name: "rating",
            title: "Rating"
        },
        {
            data: null,
            title: "Actions",
            orderable: false,
            render: function(data, type, row) {
                return getActionButtons(row.movie_id, 'movie');
            }
        }
    ]
});

//screening tables
new DataTable("#screeningTable", {
    ajax: baseUrl() + "/ScreeningsManagement/DataTables",
    processing: true,
    serverSide: true,
    columns: [
        {
            data: "screening_id",
            name: "screening_id",
            title: "Screening ID"
        },
        {
            data: "cinema_id",
            name: "cinema_id",
            title: "Cinema ID"
        },
        {
            data: "movie_id",
            name: "movie_id",
            title: "Movie ID"
        },
        {
            data: "screening_time",
            name: "screening_time",
            title: "Screening Time"
        },
        {
            data: null,
            title: "Actions",
            orderable: false,
            render: function(data, type, row) {
                return getActionButtons(row.screening_id, 'screening');
            }
        }
    ]
});

//booking tables
new DataTable("#bookingTable", {
    ajax: baseUrl() + "/BookingsManagement/DataTables",
    processing: true,
    serverSide: true,
    columns: [
        {
            data: "booking_id",
            name: "booking_id",
            title: "Booking ID"
        },
        {
            data: "customer_id",
            name: "customer_id",
            title: "Customer ID"
        },
        {
            data: "screening_id",
            name: "screening_id",
            title: "Screening ID"
        },
        {
            data: "set_number",
            name: "set_number",
            title: "Set Number"
        },
        {
            data: "status",
            name: "status",
            title: "Status",
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
                return `<span class="px-2 py-1  text-white text-sm rounded-full ${colorClass}">${data}</span>`;
            }
        },
        {
            data: null,
            title: "Actions",
            orderable: false,
            render: function(data, type, row) {
                return getActionButtons(row.booking_id, 'booking');
            }
        }
    ]
});
