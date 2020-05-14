let locale = (typeof global_locale !== 'undefined')? global_locale : 'en'

let dictionary = require('../i18n/'+locale+'.json');

let t_ = function(key){
    return deepFind(dictionary, key)
}

function deepFind(obj, path) {
    var paths = path.split('.')
    var current = obj
    var i
  
    for (i = 0; i < paths.length; ++i) {
      if (current[paths[i]] == undefined) {
        return undefined;
      } else {
        current = current[paths[i]];
      }
    }
    return current;
  }

export {
    locale,
    t_
}