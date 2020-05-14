<template>
  <div id="modal">
    <div class="overlay" v-if="modalVisible">
      <div class="overlay-wrapper entry-content">   
        <button class="close" @click="closeModal()">
          <svg id="Group_75" data-name="Group 75" xmlns="http://www.w3.org/2000/svg" width="37" height="37" viewBox="0 0 37 37">
            <g id="Group_74" data-name="Group 74">
              <path id="Path_20" data-name="Path 20" d="M18.5,37A18.5,18.5,0,1,0,0,18.5,18.521,18.521,0,0,0,18.5,37Zm0-35.152A16.652,16.652,0,1,1,1.848,18.5,16.67,16.67,0,0,1,18.5,1.848Z" fill="#fff"/>
              <path id="Path_21" data-name="Path 21" d="M176.972,186.765a.929.929,0,0,0,1.313,0l3.591-3.591,3.591,3.591a.926.926,0,1,0,1.313-1.305l-3.606-3.591,3.591-3.591a.923.923,0,1,0-1.305-1.305l-3.591,3.591-3.591-3.591a.923.923,0,0,0-1.305,1.305l3.591,3.591-3.591,3.591A.92.92,0,0,0,176.972,186.765Z" transform="translate(-163.368 -163.368)" fill="#fff"/>
            </g>
          </svg>
        </button>      
        <div class="img_gallery">
            <div class="selected">
              <img :src="item.guid" />
              <div class="selected-blur" :style="{backgroundImage: `url(${item.guid})`}"></div>
            </div>
            
          <div class="side" v-if="this.images!= null && this.images.length > 0">
            <div class="images">
              <div class="image-wrapper" v-for="image in this.images" @click="updateModal(image)">
                <div :class="{'image': true, 'active': image.ID === item.ID}">
                  <div :style="{backgroundImage: `url(${image.thumbnail})`}"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="infos">    
          <h4 class="headline">{{ item.post_title }}</h4>
        <ul>
          <li class="type-of-place">
            <div>
              <span>{{t_('image_info.description')}}</span>
              <p>{{ item.post_content }}</p>
            </div>
          </li>
          <li v-if="item.meta && item.meta.shap_place_name && item.meta.shap_place_name[0]" class="type-of-place">
            <div>
              <span>{{t_('image_info.place')}}</span>
              <p>{{ item.meta.shap_place_name[0] }}</p>
            </div>
          </li>
          <li v-if="item.meta && item.meta.shap_period && item.meta.shap_period[0]" class="">
            <div>
              <span>{{t_('image_info.period')}}</span>
              <p>{{ item.meta.shap_period[0] }}</p>
            </div>            
          </li>
          <li v-if="item.meta && item.meta.shap_type_of_subject && item.meta.shap_type_of_subject[0]" class="">
            <div>
              <span>{{t_('image_info.type_of_subject')}}</span>
              <p>{{ item.meta.shap_type_of_subject[0] }}</p>
            </div>            
          </li>
          <li v-if="item.pool && item.pool[0] && item.pool[0].name" class="">
            <div>
              <span>{{t_('image_info.collection')}}</span>
              <p>{{ item.pool[0].name }}</p>
            </div>            
          </li>
          <li v-if="item.meta && item.meta.shap_original_datum && item.meta.shap_original_datum[0]" class="">
            <div>
              <span>{{t_('image_info.year_created')}}</span>
              <p>{{ formatDate(item.meta.shap_original_datum[0]) }}</p>
            </div>            
          </li>
          <li v-if="item.meta && item.meta.shap_author && item.meta.shap_author[0]" class="">
            <div>
              <span>{{t_('image_info.author')}}</span>
              <p>{{ item.meta.shap_author[0] }}</p>
            </div>            
          </li>
          <li v-if="item.meta && item.meta.shap_copyright_vermerk && item.meta.shap_copyright_vermerk[0]" class="">
            <div>
              <span>{{t_('image_info.copyright')}}</span>
              <p>{{ item.meta.shap_copyright_vermerk[0] }}</p>
            </div>            
          </li>
          <li v-if="item.meta && item.meta.shap_easydb_id && item.meta.shap_easydb_id[0]" class="">
            <div>
              <span>{{t_('image_info.easydb_id')}}</span>
              <p><a :href="'http://syrianheritage.gbv.de/detail/'+item.meta.shap_easydb_id[0]" target="_blank" rel="noopener noreferrer">#{{ item.meta.shap_easydb_id[0] }}</a></p>
            </div>            
          </li>
        </ul>
      </div>
    </div>
  </div>
  </div>
</template>

<script>
import { bus } from '../main'
import { disableBodyScroll, enableBodyScroll, clearAllBodyScrollLocks } from 'body-scroll-lock';
import moment from 'moment'

const targetElement = document.querySelector("#modal");

export default {
  data: function(){
    return {
      //modalVisible: true
    }
  },
  name: 'Modal',
  props: ['item', 'images', 'modalVisible'],
  methods: {
      updateModal(image){
          bus.$emit('updateModal', image);
      },
      toggleModal(){
        this.modalVisible = !this.modalVisible;

      },
      closeModal(){
        bus.$emit('closeModal');
        enableBodyScroll(targetElement);
        
      },
      openModal(){
        //this.modalVisible = true;
        bus.$emit('openModal');
      },
        // methods area
      formatDate: function(dt){

        return moment(dt).format('YYYY');

      }
  }
}
</script>
