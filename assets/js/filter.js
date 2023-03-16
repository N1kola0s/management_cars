import {URL_HOST}  from './env_variables.js';

const form = document.querySelector('form');

const serializeForm = function (form) {
    let obj = {};
	let formData = new FormData(form);
	for (let key of formData.keys()) {
        obj[key] = formData.get(key);
	}
	return obj;
};


//imposto listener di eventi per rilevare quando i moduli vengono inviati
document.querySelector('#submit').addEventListener('click', function (event) {
    
    
    event.preventDefault();
    

    //seleziono il container della tabella;
    let tableContainer = document.getElementById('table_container');

    //pulisco la tabella precedente;
    tableContainer.textContent = "";
    
    //console.log(event.target);
    //let data = serializeForm(event.target);
    let data = serializeForm(form);
    //console.log(data);
    

    let url = new URL(URL_HOST + "app/api/distances_list.php");


    for (let element in data) { 
        if(data[element] != ""){
            
            url.searchParams.append(element, data[element]); 
        }
    }

    console.log(url);


	get_data_table(url);

});




