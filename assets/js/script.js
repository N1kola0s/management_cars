import {URL_HOST}  from './env_variables.js';

function get_data_table (url){


    let distances;

    let tableContainer = document.getElementById('table_container');

    fetch(url, {
        method: 'GET',
        header: {
            'Content-Type' : 'application/json'
        }
    })
    .then(response => response.json())
    .then(data_distances => {
        distances = data_distances;
        console.log('Received data: ', data_distances);
        let table = `
        <table>

            <thead>
                <th>ID</th>
                <th>TARGA</th>
                <th>MESE</th>
                <th>DISTANZA PERCORSA(KM)</th>
                <th>EMAIL</th>
            </thead>


            <tbody>
             ${rowsGenerator(data_distances)}
            </tbody>

        </table>

        `;

        tableContainer.insertAdjacentHTML('beforeend', table);

    })
    .catch((error) => {
        console.error('Error: ', error);
    });

    function rowsGenerator(distances){
        let rows = '';
        distances.forEach(distance => {
            let row = `
            <tr>
                <td>${distance.id}</td>
                <td>${distance.car_license_plate}</td>
                <td>${distance.car_rental_month}</td>
                <td>${distance.km}</td>
                <td>${distance.email}</td>
            </tr>
            `;

            rows += row;
        });

        return rows;

    }

}

get_data_table(URL_HOST + 'app/api/distances_list.php');





