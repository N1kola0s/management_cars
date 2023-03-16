<template>
 

    <header>

        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            
            <div class="px-4 container-fluid">

            <div class="navbar-brand text-primary">Gestione flotte</div>

            <div>
                <router-link class="pe-2" to="/">Login</router-link> |
                <router-link class="ps-2" to="/admin">Admin</router-link>
            </div>
            

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
            </button>
            
            </div>
            <!-- /.container-fluid -->

        </nav>
        <!-- /nav -->

    </header>
    <!-- /header -->
    


</template>

<script>


export default {
  name: 'SiteHeader',
  components: {
    
  }
}
</script>

<style scoped>

    a,
    .nav-link{
        font-size: 16px;
        text-decoration: none !important;
        font-weight: bold !important;
        color: rgb(0, 98, 255) !important;
        cursor:pointer;
    }


    .navbar{
        font-size:1.2rem;
        box-shadow: 0 2px 4px 0 rgb(0 0 0/ 15%);
    }



</style>