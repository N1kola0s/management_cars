import {URL_HOST}  from './env_variables.js';

const param = window.location.search;
//console.log(param);
const urlParams = new URLSearchParams(param);
const auth_token = urlParams.get('auth_token');
//console.log(auth_token);



/* axios
    ({
        method: 'put',
        url: "http://localhost:8000/app/api/distances_validation.php?id=" + insert_id,  
        headers: {},
        data: {
            'validation' :'valid'
        }
    })
    .then((response) => {console.log(response)})
    .catch((err) => {console.log(err)}) */


axios.put(URL_HOST + "app/api/distances_validation.php?auth_token=" + auth_token, {
    
    validation : 'valid'
})
    .then((response) => {           
        //console.log(response.data.message);

        let confirmation_message = document.querySelector(".confirm_data_message");

        if(response.data.success){

            const icon = "<i class='icon_confirm fa-solid fa-circle-check'></i>";
            confirmation_message.insertAdjacentHTML("beforebegin", icon);

        }else {

            const icon = "<i class=' icon_not_confirm fa-regular fa-circle-xmark'></i>";
            confirmation_message.insertAdjacentHTML("beforebegin", icon);

        }

        confirmation_message.textContent = response.data.message;
    
    })
    .catch((err) => {console.log(err)}) 