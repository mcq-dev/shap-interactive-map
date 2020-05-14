<template>

  <div id="map-header">
    <div class="entry-content">
      <FilterDropdown v-bind:filter_list="filter_list.time" filter_type='time' :placeholder="t_('filter.time')" requestServerSide=True />
      <FilterDropdown v-bind:filter_list="filter_list.place" filter_type='place' :placeholder="t_('filter.place')" />
      <FilterDropdown v-bind:filter_list="filter_list.placetype" filter_type='placetype' :placeholder="t_('filter.place_type')" />
      <FilterDropdown v-bind:filter_list="filter_list.fileclass" filter_type='fileclass' :placeholder="t_('filter.file_type')" requestServerSide=True />
    </div>
  </div>
  
</template>

<script>
import { bus } from '../main'
import FilterDropdown from './FilterDropdown.vue'
import { fetchFilterList } from '../utils/connection'

export default {
  name: 'Filters',
  components: {
    FilterDropdown
  },
  data: function () {
	  return {
      filter_list: {
        time: [],
        place: []
      } 
	  };
	},
  created (){
    fetchFilterList().then((filters)=>{
            filters.time = filters.time.filter((item)=>{
               return /[a-z]+/ig.test(item)
            })
            this.filter_list = filters
          })
  }
}

</script>

<style scoped lang="scss">

</style>
