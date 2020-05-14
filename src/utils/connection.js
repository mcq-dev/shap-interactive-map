import { locale } from './i18n'
import { config } from '../config'
const wp_url = config.wp.url;

function encodeQueryData(data){
    const ret = [];
    for (let d in data){
        ret.push(encodeURIComponent(d) + '=' + encodeURIComponent(data[d]));
    }
    return ret.join('&');
}

let fetchPlaces = function(filters){
    let queryParams = {
        lang: locale,
        ...filters
      }
    let querystring = encodeQueryData(queryParams)
    console.log(querystring)

    return fetch( wp_url + '/wp-json/map/v1/places?' + querystring, {headers: { 'Accept': 'application/json' }})
                    .then((resp)=>{return resp.json()})
                    .then((json)=>{return json})
}

let fetchImagesOfPlace = function(imageId){
    let queryParams = {
        lang: locale
      }
    let querystring = encodeQueryData(queryParams)

    return fetch( wp_url + '/wp-json/map/v1/images_of_place/'+imageId+'?'+querystring,{headers: { 'Accept': 'application/vnd.geo+json' }})
                .then((resp)=>{return resp.json()})
                .then((json)=>{return json})
}

let fetchFilterList = function(){
    return fetch( wp_url + '/wp-json/map/v1/filters?lang='+locale ,{headers: { 'Accept': 'application/json' }})
    .then((resp)=>{return resp.json()})
    .then((json)=>{return json})
}

export {
    fetchPlaces,
    fetchImagesOfPlace,
    fetchFilterList
}