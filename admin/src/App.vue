<template>
  <div id="app">
  <!--   <nav>
      <router-link to="/">Login</router-link> |
      <router-link to="/admin">Admin</router-link>
    </nav>
    <router-view/> -->

    <SiteHeader/>
    <SiteMain/>
    <SiteFooter/>
    
  </div>
</template>

<script>

import SiteHeader from '@/components/HeaderComponent.vue'
import SiteFooter from '@/components/FooterComponent.vue'
import SiteMain from '@/components/MainComponent.vue'

  export default {
    components: {
      SiteHeader,
      SiteMain,
      SiteFooter
      
    }
  }
</script>


<style>
  /* #app {
    font-family: Avenir, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
  }

  nav {
    padding: 30px;
  }

  nav a {
    font-weight: bold;
    color: #2c3e50;
  }

  nav a.router-link-exact-active {
    color: #42b983;
  } */


  /* #region VARIABLE */

  *{
      margin: 0;
      padding:0;
      box-sizing: border-box;
  }

  :root{

      --bg_primary: #d9DADC;
      --bg_secondary: #ffffff;
      --bg_footer: black;
      --txt_secondary: white;
  }

  /* #endregion */

  /* #region COMMON */

  body{
      height:100vh;
  }

  header,
  footer{
      height:6vh;
  }



  main{
      height: 88vh;
      background-color: var(--bg_secondary);
      overflow-y: auto;
  }

</style>
