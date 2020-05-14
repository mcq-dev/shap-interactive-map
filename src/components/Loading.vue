<template>
  <div id="loading" v-if="loadingVisible.length > 0">
    <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
  </div>
</template>

<script>
import { bus } from '../main'

export default {
  name: 'Loading',
  props: ['loadingVisible'],
  data: function(){
    return {
      //loadingVisible: true
    }
  }
}

</script>

<style scoped lang="scss">
    #loading {
        width: 100%;
        height: 100%;
        position: fixed;
        top: 0;
        left: 0;
        background: rgba(255,255,255, 0.8);
        color: #fff;
        overflow: hidden;
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        // display: none;
        // &.show {
        //   display: block;
        // }
    }
</style>
