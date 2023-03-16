<template>
  <div class="admin">
    
    <div class="container py-5">
      <b-form class="d-flex align-items-center justify-content-between" @reset="onReset">
        <b-form-group
          id="input-group-1"
          label="Targa:"
          label-for="car_license_plate"
          
        >
          <b-form-input
            id="car_license_plate"
            class="py-2 my-3"
            v-model="filters['car_license_plate']"
            type="text"
            @keyup="callApi"
            
          ></b-form-input>

        </b-form-group>
        <!-- /#input_license_plate_group -->

        <b-form-group
        id="input-rental_month_group" 
        label="Mese:" 
        label-for="car_rental_month">

          <date-picker 
          @change="callApi"
          class="py-2 my-2"
          type="month" 
          v-model="filters['car_rental_month']"
          format="MMMM YYYY"
          valueType="YYYY-MM-DD"
          ></date-picker>

        </b-form-group>
        <!-- /#input_rental_month_group -->

        <b-form-group 
          id="input_validation_group" 
          label="Validazione:" 
          label-for="Validato">

          <b-form-select
          @change="callApi"
          id="validation"
          class="py-2 my-3"
          v-model="filters['validation']"
          :options="options"
            
          ></b-form-select>

        </b-form-group>
        <!-- /#input_validation_group -->
        <div>
          <b-button  class="py-2 me-2 mt-4" type="reset" variant="danger">Reset</b-button>
          <b-button  class="py-2 mt-4"  @click.prevent="downloadSpreadsheet" variant="success">Scarica Excel</b-button>
        </div>


      </b-form>
      <!-- /b-form -->

    </div>
    <!-- /.container -->

    <div class="container py-3">


      <b-table 
          striped
          hover
          :bordered ="bordered"
          :items = distances
          :fields ="fields"
          :current-page="pagination.currentPage"
          :per-page ="0">
          
        </b-table>
        <b-pagination
          class="pt-4"
          v-model="pagination.currentPage"
          :total-rows="totalPages"
          align="center"
          :per-page="pagination.perPage"
          @change="pageHandler">
        </b-pagination>
    </div>

  </div>
</template>

<script>
  import axios from "axios";
  import moment from "moment";
  import DatePicker from 'vue2-datepicker';
  import 'vue2-datepicker/index.css';
  import 'vue2-datepicker/locale/it';
  

  export default {
    name: 'AdminPage',
    components: {
      DatePicker
    },
    data() {
      return {
        url: `${process.env.VUE_APP_URL_API}distances_list.php`,
        downloadUrl: `${process.env.VUE_APP_URL_API}distances_download.php`,
        distances: null,
        totalPages: null,
        bordered: true,
        error: null,
        options: [
          { text: "Seleziona", value: null, disabled: true},
          { text: 'Tutti', value: ""},
          { text: 'Sì', value: 'valid' },
          { text: 'No', value: 'not_valid' }
        ],
        filters:{},
        pagination:{
          currentPage:1,
          perPage:20
        },
        fields: [
          {
            key: "id",
            label: "ID",
          },
          {
            key: "car_license_plate",
            label: "Targa",
            formatter: (value) => {
              return value.toUpperCase();
            }
          },
          {
            key: "car_rental_month",
            label: "Mese / Anno",
            formatter: (value) => {
              //formatto la data in input nel formato desiderato ed in lingua italiana ( es. 'gennaio 2021');
              return moment(value).locale('it').format('MMMM YYYY');
            },
           
          },
          {
            key: "km",
            label: "Distanza ( km )"
          },
          {
            key: "validation",
            label: "Validato",
            formatter: (value) => {
              //formatto i valori del campo 'validation' ricevuti nel formato desiderato in tabella;
              if(value === 'valid'){
                return value = 'Sì';
              } else {
                return value = 'No';
              }
            },
          }
        ]
      } 
    },
    methods: {
      cleanFilters(){
        //metodo per pulire la struttura dati in caso di cancellazione: quindi nei casi limiti in cui il valore sia nullo,indefinito o una stringa vuota;

        let filters = this.filters

        for (const key in filters) {
          if (Object.hasOwnProperty.call(filters, key)) {
            
            if (filters[key] === null || filters[key] === undefined || filters[key] === "") {
              delete filters[key];
            }
          }
        }

        return this.filters = filters;
  
      },
      onReset(){
        //metodo per il reset dei dati in params;

        this.filters= {}
        //metodo per richiesta axios di tutti i dati;
        this.callAllData();
      },
      callApi(){
        
        //invoca metodo per pulire dati in params;
        this.cleanFilters();

        //richiesta axios all'api per recuperare i dati in base a dei parametri definiti nel metodo;
        axios
        .get(this.url, {
          //impostazione oggetto da passare nel metodo GET della richiesta;
          params: {
            data:this.filters,
            pagination: {
              currentPage : this.pagination.currentPage,
              perPage: this.pagination.perPage
            }
          }
        })
        .then(response =>{
          this.distances = response.data.data;
          this.totalPages = response.data.count_rows;
        })
        .catch(error => {
          this.error = `Si è verificato un problema. ${error}`
        })
      
        
      },
      pageHandler(page){
        //metodo per gestire la paginazione in modo dinamico;

        this.pagination.currentPage = page;
        this.callApi();
      },
      callAllData(){

        //richiesta axios all'api per recuperare tutti i dati;
        axios
        .get(this.url, {
          //impostazione oggetto da passare nel metodo GET della richiesta 
            params:{
              data:this.filters,
              pagination: {
                currentPage : this.pagination.currentPage,
                perPage: this.pagination.perPage
              }
            }
          })
        .then(response =>{
          this.distances = response.data.data;
         
          this.totalPages = response.data.count_rows;
        })
        .catch(error => {
          this.error = `Si è verificato un problema. ${error}`
        })
      },
      downloadSpreadsheet(){
        
        //richiesta axios all'api per recuperare tutti i dati;
        axios
        .get(this.downloadUrl, {
          //impostazione oggetto da passare nel metodo GET della richiesta;
          params: {
            data:this.filters,
            pagination: {
              currentPage : this.pagination.currentPage,
              perPage: this.pagination.perPage
            }
          }
        } )
        .then(response =>{

          // creazione "a" elemento HTML con href al file & il click
          const link = document.createElement('a');
          link.href = `${process.env.VUE_APP_URL_API}/${response.data.path}`;
          link.setAttribute('download', 'dati_gestione_flotte.xlsx');
          document.body.appendChild(link);
          link.click();

          // pulisco elemento "a"
          document.body.removeChild(link);
              
        })
        .catch(error => {
          this.error = `Si è verificato un problema. ${error}`
        })
      }

    },
    mounted(){
      //richiesta axios di tutti i dati;
      this.callAllData();
    },
  };
</script>

<style scoped>




</style>