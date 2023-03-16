import {URL_HOST}  from './env_variables.js';

//settaggi datepicker;
$("#datepicker").datepicker( {
    format: "MM yyyy",
    startView: "months", 
    minViewMode: "months",
    startDate: "-12m",
    endDate: "0m",
    language:"it",
    todayHighlight: true
});


//inizializzaione variabile con selezione del form;
const form = document.querySelector('form');
//loader element;
const loader = document.querySelector(".spinner-border");

loader.classList.add("d-none");

const container_message = document.getElementById("container_message");
   
container_message.innerHTML = `<div class="mess alert mt-3" role="alert"></div>`;


//evento al click del button per invio dati dal form;
document.querySelector('#insertBtn').addEventListener('click', async function (event) {
 
    event.preventDefault();


    //inizializzazione variabile con istanza del form; 
    const formData = new FormData(form);

    //console.log(formData);
    formData.append('created_by', 1);

    //dichiaro costante con elemento bottone submit
    const button = document.getElementById("insertBtn");

    //disattivazione tasto invio dati prima della chiamata
    button.setAttribute('disabled', "");
    
    //faccio il check per verificare se esiste l'elemento con classe mess
    if (!document.body.contains(document.querySelector(".mess"))){
        
        container_message.innerHTML = `<div class="mess alert mt-3" role="alert"></div>`;

        
    }
     
    //seleziono elemento DOM del messaggio
    const mess = document.querySelector(".mess");
    //pulisco l'alert con contenuto messaggio
    mess.classList.remove("alert-danger");
    //pulisco la stringa contenuta come messaggio
    mess.textContent = ""; 
    
    
    //richiesta axios con metodo POST dei dati;
    const res = await axios.post(URL_HOST + "app/api/distances_create.php", formData)
    .then((res) => {
        
        //rimuovo la classe alert di errore
        mess.classList.remove("alert");
        
        //rendo visibile elemento loader 
        loader.classList.remove("d-none");
        
        setTimeout(function(){
            //cambio il colore del tasto invia
            button.classList.remove("btn-success");
            button.classList.add("btn-primary");
            
            //aggiungo messaggio di risposta
            mess.textContent = res.data.message; 
            //rendo visibile elemento loader 
            loader.classList.add("d-none");

            //aggiungo la classe alert di errore
            mess.classList.add("alert");
            
                
            //riattivazione tasto invio 
            button.removeAttribute('disabled');  

            
       
            //in caso di risposta negativa
            if(!res.data.success){
    
                //rimuovo la classe alert di successo
                mess.classList.remove("alert-success");
                //aggiungo la classe alert di errore
                mess.classList.add("alert-danger");
        
            } else {
                //in caso di risposta positiva:
    
                //faccio il reset del form
                document.querySelector('form').reset();
                //aggiungo al messaggio che arriverà l'email per conferma dati
                mess.textContent += ". A breve riceverai un'email di conferma.";
                //rimuovo classe dell'alert di errore
                mess.classList.remove("alert-danger");
                //aggiungo classe alert di successo
                mess.classList.add("alert-success", "alert-dismissible", "fade", "show");
                
                
                mess.innerHTML += `
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                    </button> 
                `; 

                //cambio il colore del tasto invia
                button.classList.remove("btn-primary");
                button.classList.add("btn-success");   
        
            }

        }, 400);  
    
    })
    .catch((err) => {console.log(err)})   


    
});


