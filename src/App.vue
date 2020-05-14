<template>
  <div id="app">
    <div id='menu' style="display: none">
      <input id='satellite-v9' type='radio' name='rtoggle' value='satellite'>
      <label for='satellite'>satellite</label>
    </div>
    <div>
      <Filters />
      <div class="map-wrapper">
        <Map />
        <Sidebar v-bind:item="item" v-bind:images="images" />
      </div>
    </div>
    <Modal v-bind:item="modalImage" v-bind:images="images" v-bind:modalVisible="modalVisible" />
    <Loading v-bind:loadingVisible="loadingVisible"/>
  </div>
  
</template>

<script>
import { bus } from './main'
import Sidebar from './components/Sidebar.vue'
import Map from './components/Map.vue'
import Modal from './components/Modal.vue'
import Loading from './components/Loading.vue'
import Filters from './components/Filters.vue'
import { fetchPlaces, fetchImagesOfPlace } from './utils/connection'

export default {
  name: 'app',
  components: {
    Sidebar,
    Map,
    Modal,
    Loading,
    Filters
  },
  data: function () {
	  return {
	  	item: {
        name: '',
        description: '',
        //imgs: ['a','b'],
        meta: {}
      },
      images: [
        {guid: ""}
      ],
      modalImage: {},
      modalVisible: false,
      activeFilters: {
        server: {},
        client: {}
      },
      loadingVisible: [],
      geoJsonOriginal: {},
      geoJsonServerFiltered: {}
	  };
  },
  methods: {
    fetchMapData(){
      return fetchPlaces(this.activeFilters.server)
    },
    getFilteredDataFromServer(){
        this.loadingVisible.push('token');
        this.fetchMapData().then((data)=>{
            if(Object.keys(this.geoJsonOriginal).length === 0){
              this.geoJsonOriginal = data
            }
            this.geoJsonServerFiltered = data
            this.filterThroughClientFilters()
            this.loadingVisible.pop()
        })
    },
    filterThroughClientFilters(){
        let geoJson = JSON.parse(JSON.stringify(this.geoJsonServerFiltered))
        // keep places with right name and right placetype
        // if filter isn't set (null) return the item
        if(this.activeFilters.client.place || this.activeFilters.client.placetype){
          geoJson.features = geoJson.features.filter((place)=>{
            return (this.activeFilters.client.place === undefined || place.properties.name === this.activeFilters.client.place) &&
                    (this.activeFilters.client.placetype === undefined || (place.properties.meta['place_type'] && place.properties.meta['place_type'].length > 0 && place.properties.meta['place_type'][0] === this.activeFilters.client.placetype))
          })
        }
        bus.$emit('updateMapData', geoJson)
    }
  },
  created (){
    this.getFilteredDataFromServer()

    bus.$on('updateSidebar', (data) => {
      this.loadingVisible.push('token');
      if(data.term_id){
          fetchImagesOfPlace(data.term_id).then((a)=>{
            this.images = a
            this.loadingVisible.pop();
          })
      }
      if(data.meta && typeof(data.meta) == 'string'){
        data.meta = JSON.parse(data.meta)
      }
      this.item = data;
    })
        
    bus.$on('updateModal', (data) => {
      this.modalImage = data;
    })

    bus.$on('openModal', () => {
      this.modalVisible = true;
    })

    bus.$on('closeModal', () => {
      this.modalVisible = false;
    })

    bus.$on('updateSelectedFilter', (data) => {
      // filter object struct (data):
      // {type: this.filter_type, selected: filterName, requestServerSide: True}
      
      // Update the filter client side or server side.
      // If requestServerSide is true, send a request to the server and wait for the response of a filtered geoJson.
      // Than it calls the filterThroughClientFilters() function, that update the results in the browser.
      // If a client side filter is passed, than it calls only the filterThroughClientFilters() function without a request to the server.
      if(data.requestServerSide){
        if(data.selected){
          this.activeFilters.server[data.type] = data.selected
        }else{
          delete this.activeFilters.server[data.type]
        }
        this.getFilteredDataFromServer()
      }else{
        if(data.selected){
          this.activeFilters.client[data.type] = data.selected
        }else{
          delete this.activeFilters.client[data.type]
        }
        this.filterThroughClientFilters()
      }
    })
  }
}

</script>

<style scoped lang="scss">
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  direction: ltr;
  margin-bottom: 20px;
}

.map-wrapper {
  position: relative;
}
</style>
