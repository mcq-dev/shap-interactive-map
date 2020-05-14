<template>
  <div id="map">

  </div>
</template>

<script>
import { bus } from '../main'
import * as mapboxgl from 'mapbox-gl'
import {locale} from '../utils/i18n'
import {config} from '../config'

export default {
  name: 'Map',
  props: ['item'],
  data() {
	  return {
      map: null,
      popup: null,
      geojsonData: {
              type: "FeatureCollection",
              crs: { type: "name", properties: { name: "urn:ogc:def:crs:OGC:1.3:CRS84" } },
              features: []
            }
	  };
	},
  mounted: function(){
      this.createMap()
  },
  created(){
      bus.$on('updateMapData', (data)=>{

          this.geojsonData = data

          // wait till localplaces is created
          // if localplaces doens't exist yet, retry each 50ms
          if(this.map.getSource('localplaces')){
            this.updateData(this.geojsonData)
          }else{
            let updateInterval = window.setInterval((data)=>{
              if(this.map.getSource('localplaces')){
                this.updateData(this.geojsonData)
                clearInterval(updateInterval)
              }
            }, 50);
          }

      })
  },
  methods: {
      updateSidebar (data){
          bus.$emit('updateSidebar', data);
      },
      updateData(data){
          // remove the old popup, before new on is created
          this.popup && this.popup.remove()

          this.map.getSource('localplaces').setData(data);
          // set current item to a random place from the list
          if(data.features.length >= 1){
            let random = Math.floor(Math.random() * (data.features.length - 0) + 0)
            this.selectItem(data.features[random])
          }
      },
      // Select a item: Open the item info in the sidebar and a popup box on the target on the map
      selectItem(item){
            this.updateSidebar(item.properties)

            var coordinates = item.geometry.coordinates.slice();
            var description = '<h4>' + item.properties.name + '</h4>';
            
            // Ensure that if the map is zoomed out such that multiple
            // copies of the feature are visible, the popup appears
            // over the copy being pointed to.
            // while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
            //   coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
            // }
            
            this.popup = new mapboxgl.Popup()
            .setLngLat(coordinates)
            .setHTML(description)
            .addTo(this.map);
            
            this.map.panTo(coordinates);
      },
      createMap(){

        let language_styles = config.mapbox.languageStyle

        let style = (language_styles[locale])? language_styles[locale] : language_styles['en']

        mapboxgl.accessToken = config.mapbox.accessToken;
        this.map = new mapboxgl.Map({
          container: 'map',
          style: style,
          center: [38, 35], // starting position
          zoom: 6 // starting zoom
        });

        this.map.addControl(new mapboxgl.NavigationControl());
        mapboxgl.setRTLTextPlugin('https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-rtl-text/v0.2.0/mapbox-gl-rtl-text.js');


        var layerList = document.getElementById('menu');
        var inputs = layerList.getElementsByTagName('input');
        
        function switchLayer(layer) {
          var layerId = layer.target.id;
          this.map.setStyle('mapbox://styles/mapbox/' + layerId);
        }
        
        for (var i = 0; i < inputs.length; i++) {
          inputs[i].onclick = switchLayer;
        }

        // bounding box (N-lat:37, E-lon:42) (S-lat: 32, W-lon:36)
        var sourceGazetteer = fetch('https://gazetteer.dainst.org/search?limit=10&bbox=37&bbox=42&bbox=32&bbox=36',{headers: { 'Accept': 'application/vnd.geo+json' }})
                .then((resp)=>{return resp.json()})
                .then((json)=>{return json})
                .then((a)=>{
                  return a
                })

        this.map.on('load', ()=>{

          // Add a new source from our GeoJSON data and set the
          // 'cluster' option to true. GL-JS will add the point_count property to your source data.

          this.map.addSource("localplaces", {
            type: "geojson",
            // Point to GeoJSON data
            data: this.geojsonData,
            //headers: { 'Accept': 'application/vnd.geo+json' },
            cluster: true,
            clusterMaxZoom: 14, // Max zoom to cluster points on
            clusterRadius: 50 // Radius of each cluster when clustering points (defaults to 50)
          })
          this.map.addLayer({
            id: "clusters",
            type: "circle",
            source: "localplaces",
            filter: ["has", "point_count"],
            paint: {
              // Use step expressions (https://docs.mapbox.com/mapbox-gl-js/style-spec/#expressions-step)
              // with three steps to implement three types of circles:
              //   * Blue, 20px circles when point count is less than 100
              //   * Yellow, 30px circles when point count is between 100 and 750
              //   * Pink, 40px circles when point count is greater than or equal to 750
              "circle-color": [
                "step",
                ["get", "point_count"],
                "#fff",
                100,
                "#fff",
                750,
                "#fff"
              ],
              "circle-radius": [
                "step",
                ["get", "point_count"],
                20,
                100,
                30,
                750,
                40
              ]
            }
          });
            
          this.map.addLayer({
            id: "cluster-count",
            type: "symbol",
            source: "localplaces",
            filter: ["has", "point_count"],
            layout: {
              "text-field": "{point_count_abbreviated}",
              "text-font": ["DIN Offc Pro Medium", "Arial Unicode MS Bold"],
              "text-size": 12,
            },
            paint: {
              "text-color": "#000"
            }
          });
            
          this.map.loadImage(
            require('../assets/pin.png'),
            (error, image) => {
              if (error) throw error;
              this.map.addImage('pin', image);
              this.map.addLayer({
                'id': 'unclustered-point',
                'type': 'symbol',
                'source': 'localplaces',
                'filter': ["!", ["has", "point_count"]],
                'layout': {
                  'icon-image': 'pin',
                  'icon-size': 0.30
                }
              });
            }
            );

            
          // inspect a cluster on click
          this.map.on('click', 'clusters', (e) => {
            var features = this.map.queryRenderedFeatures(e.point, { layers: ['clusters'] });
            var clusterId = features[0].properties.cluster_id;
            this.map.getSource('localplaces').getClusterExpansionZoom(clusterId, (err, zoom) => {
              if (err)
              return;
              
              this.map.easeTo({
                center: features[0].geometry.coordinates,
                zoom: zoom
              });
            });
          });



          // When a click event occurs on a feature in the unclustered-point layer, open a popup at the
          // location of the feature, with description HTML from its properties.
          this.map.on('click', 'unclustered-point', (e) =>{
            this.selectItem(e.features[0])
          });
            
          this.map.on('mouseenter', 'clusters', () => {
            this.map.getCanvas().style.cursor = 'pointer';
          });
          this.map.on('mouseleave', 'clusters', () => {
            this.map.getCanvas().style.cursor = '';
          });

          this.map.on('mouseenter', 'unclustered-point', () => {
            this.map.getCanvas().style.cursor = 'pointer';
          });
          this.map.on('mouseleave', 'unclustered-point', () => {
            this.map.getCanvas().style.cursor = '';
          });
        });
      }
  }
}



</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
  @import '~mapbox-gl/dist/mapbox-gl';

  #map{
    direction: ltr;
  }
</style>
