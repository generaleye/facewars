/**
 * Created by root on 11/4/15.
 */

(function(){

    this.BASE_URL_LOCAL = "http://localhost/oaufacewars/";
    this.BASE_URL_SERVER = "http://oau-facewars.rhcloud.com/";
    if (window.location.hostname == 'localhost') {
        BASE_URL = BASE_URL_LOCAL;
    } else {
        BASE_URL = BASE_URL_SERVER;
    }
    //if((location.href).indexOf('oau-facewars.') == -1){
    //    BASE_URL = BASE_URL_LOCAL;
    //} else {
    //    BASE_URL = BASE_URL_SERVER;
    //}
    this.API_URL = BASE_URL+"api/v1";

    //console.log(BASE_URL);
    //console.log(API_URL);
    this.URL_REGISTER = API_URL+"/register";
    this.URL_LOGIN = API_URL+"/login";

})();