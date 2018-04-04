/* 
 * (C) Copyright 2017 CEFRIEL (http://www.cefriel.com/).
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * Contributors:
 *     Andrea Fiano, Gloria Re Calegari, Irene Celino.
 */
 
'use strict';
angular.module("gwap")
.factory('gwapService', function($http){
	return{
         socialLogin: function(r,auth) {
			
			var cover = null;			
			switch(auth.network) {
				case "facebook":
					if(r.cover) {
						cover = r.cover.source;	
					}					
					break;
				case "twitter":
					cover = r.profile_background_image_url;
					break;		
				case "google":
					if(r.cover) {
						cover = r.cover.coverPhoto.url;						
					}
					break;											
			}			
			 
            return $http({
					method  : 'POST',
		            url     : 'api/userID.php', 
					data    : { 'firstName': r.first_name, 'lastName': r.last_name, 'idSocial': r.id, 'social': auth.network, 'name': r.name, 'thumbnail': r.thumbnail, 'cover': cover }, //'access_token': auth.authResponse.access_token },
		            headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
		    	})
				.then(function(result) {
					return result.data;
            });
        },
		anonymousLogin: function(ip) {

            return $http({
					method  : 'POST',
		            url     : 'api/userID.php', 
					data    : { 'firstName': 'GUEST', 'social': 'anonymous' },
		            headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
		    	})
				.then(function(result) {
					return result.data;
            });
        }
	}
})
