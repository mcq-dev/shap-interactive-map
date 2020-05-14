<template>
  <div class="shap_map_filter">
    <v-select :options="filter_list" :placeholder="placeholder" @input="updateSelectedFilter" />
  </div>
</template>

<script>
import { bus } from '../main'

import Vue from 'vue'
import vSelect from 'vue-select'

export default {
  name: 'FilterDropdown',
  props: ['filter_type', 'filter_list', 'placeholder', 'requestServerSide'],
  data: function () {
	  return {
	  	selected: ''
	  };
  },
  components: {
    'v-select': vSelect
  },
  methods: {
      updateSelectedFilter(filterName){
          this.selected = filterName
          bus.$emit('updateSelectedFilter', {type: this.filter_type, selected: filterName, requestServerSide: this.requestServerSide});
      }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
@import '~vue-select/dist/vue-select';

input.vs__search, input.vs__search:focus {
  width: 0;
  border: none;
  outline: none;
  margin: 0;
  padding: 0;
}

button.vs__clear {
  background: transparent;
  svg{
    fill: rgba(60,60,60,.5)
  }
}

.vs__dropdown-toggle {
  background: white;
  border-radius: 0;
  border-color: #2E343C;
  color: #2E343C;
  padding: 4px;
}

.v-select{
  width: 200px;
}
.vs__selected-options {
  flex-wrap: unset;
  padding: 0;
}
.vs__dropdown-menu .vs__dropdown-option {
  word-break: break-word!important;
  padding: 10px!important;
  white-space: normal!important;
  border-bottom: 1px solid rgba(0,0,0, .1);
}
.vs__selected {
  border: none;
}
.vs--single .vs__selected {
  background-color: rgba(60, 60, 60, 0.5);
  text-align: left;
  text-overflow: ellipsis;
  white-space: nowrap;
  width: 150px;
  overflow: hidden;
  display: inline-block;
  color: #fff;
  margin: 0;
}
.vs__actions {
  align-self: center;
  padding: 0;
}
.vs__actions:hover {
  cursor: pointer;
}
.vs__actions .vs__clear {
  margin-right: 5px;
}
</style>
