var easyDB = (function() {
    var baseURL = 'https://syrianheritage.gbv.de'
    var sessionID = auth()

    // if there is a token in the cookie try it out, otherwise create a new session
    function auth(){
        return getNewSessionToken(baseURL).then((sessionID)=>{
            authenticateAnonymous(baseURL,sessionID)
            return sessionID
        }).catch((err)=>{
            console.error(err)
        })
    }

    function authenticateAnonymous(baseURL,sessionID){
        return new Promise(function(resolve,reject){
            fetch(baseURL + '/api/v1/session/authenticate?token='+sessionID+'&method=anonymous',{
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }})
            .then((resp)=>{return resp.json()})
            .then((json)=>{resolve(json)})
            .catch((err)=>{reject(err)})
        })
    }

    function getNewSessionToken(baseURL){
        return new Promise(function(resolve,reject){
            fetch(baseURL + '/api/v1/session')
            .then((resp)=>{return resp.json()})
            .then((json)=>{resolve(json.token)})
            .catch((err)=>{reject(err)})
        })
    }

    function searchImgs(){
        return new Promise(function(resolve,reject){
            sessionID.then((id)=>{
                console.log(id)
                fetch(baseURL + '/api/v1/search?token='+id,{
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify( {
                        "search": [
                        ],
                        "objecttypes": [
                            "bilder"
                        ],
                        "include_fields": [
                            "bilder.beschreibung",
                            "bilder.ueberschrift",
                            "bilder._global_object_id",
                            "bilder._standard",
                            "bilder.ort_des_motivs_id._global_object_id",
                            "bilder.ort_des_motivs_id._standard",
                            "bilder.copyright_vermerk",
                            "bilder.bild"
                        ],
                        "limit": 20,
                        "format": "short"
                    } )
                })
                .then((resp)=>{return resp.json()})
                .then((json)=>{resolve(json)})
                .catch((err)=>{reject(err)})
            })
        })
    }

    return {
        searchImgs: searchImgs
    }
}());

export default easyDB
