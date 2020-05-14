<template>
  <div id="slide">
        <h3 class="headline">{{ item.name }}</h3>
        <read-more class="description" :more-str="t_('read_more')" :text="item.description" link="#" :less-str="t_('read_less')" :max-chars="200"></read-more>
        <ul>
          <li v-if="item.meta && item.meta.place_type && item.meta.place_type[0]" class="type-of-place">
            <div>
              <span>{{t_('place_type')}}</span>
              <p>{{item.meta.place_type[0]}}</p>
            </div>            
          </li>
          <li v-if="item.meta && item.meta.weitere_namen && item.meta.weitere_namen[0]" class="alternative-names">
            <div>
              <span>{{t_('alternative_names')}}</span>
              <read-more class="description" :more-str="t_('read_more')" :text="item.meta.weitere_namen[0]" link="#" :less-str="t_('read_less')" :max-chars="100"></read-more>
            </div>            
          </li>
        </ul>
        <div v-if="this.images!= null && this.images.length > 0">
          <div class="images">
						<div class="image-wrapper" v-for="image in this.images" @click="updateModal(image); openModal();">
               <div class="image" :style="{backgroundImage: `url(${image.thumbnail})`}">
                </div>
            </div>
          </div>
				</div>
      </div>
</template>

<script>
import { bus } from '../main'
import { disableBodyScroll, enableBodyScroll, clearAllBodyScrollLocks } from 'body-scroll-lock';
const targetElement = document.querySelector("#modal");

export default {
  name: 'Sidebar',
  props: ['item', 'images'],
  methods: {
      updateModal(image){
          bus.$emit('updateModal', image);
      },
      openModal(){
        bus.$emit('openModal');
        disableBodyScroll(targetElement);
      }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
h3 {
  margin: 40px 0 0;
}
ul {
  list-style-type: none;
  padding: 0;
}
li {
  display: inline-block;
  margin: 0 10px;
}
a {
  color: #42b983;
}

p {
  white-space: pre-line;
}
</style>
