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
 
angular.module("gwap", ["ngRoute", "ngCookies", "infinite-scroll", "pascalprecht.translate", "tmh.dynamicLocale", "ngDialog"])
.config(function ($routeProvider, $locationProvider, $httpProvider, $translateProvider, tmhDynamicLocaleProvider, $cookiesProvider) {

	$httpProvider.defaults.useXDomain = true;
	//$httpProvider.defaults.withCredentials = true;
	//$locationProvider.html5Mode(true);
	
	$routeProvider.when("/", {
		templateUrl: "views/home.html",
		data: { restricted: false }
	});
	
	$routeProvider.when("/home", {
		templateUrl: "views/home.html",
		data: { restricted: false }
	});

	$routeProvider.when("/myprofile", {
		templateUrl: "views/myProfile.html",
		controller: "myProfileCtrl",
		data: { restricted: true, allowAnonymous: false }
	});

	$routeProvider.when("/gameover", {
		templateUrl: "views/gameOver.html",
		data: { restricted: true, allowAnonymous: true }
	});
	
	$routeProvider.when("/badgelist", {
		templateUrl: "views/badgeList.html",
		controller: "badgeListCtrl",
		data: { restricted: true, allowAnonymous: false }
	});
	
	$routeProvider.when("/achievements", {
		templateUrl: "views/pictureList.html",
		controller: "pictureListCtrl",
		data: { restricted: true, allowAnonymous: true }
	});
	
	$routeProvider.when("/achievements/:id", {
		templateUrl: "views/pictureDetail.html",
		data: { restricted: true }
	});
	
	$routeProvider.when("/leaderboard", {
		templateUrl: "views/leaderboard.html",
		controller: "leaderboardCtrl",
		data: { restricted: true, allowAnonymous: false }
	});
	
	$routeProvider.when("/timeleaderboard", {
		templateUrl: "views/leaderboardByTime.html",
		controller: "leaderboardByTimeCtrl",
		data: { restricted: false }
	});
	
	$routeProvider.when("/game/:level", {
		templateUrl: "views/game.html",
		data: { restricted: true, allowAnonymous: true }
	});	
		
	$routeProvider.when("/login", {
		templateUrl: "views/login.html"
	});	
	
	$routeProvider.when("/connection", {
		templateUrl: "views/connection.html",
		data: { restricted: true, allowAnonymous: true }
	});
	
	$routeProvider.when("/countdown", {
		templateUrl: "views/countdown.html",
		data: { restricted: true, allowAnonymous: true }
	});	
	
	$routeProvider.when("/help", {
		templateUrl: "views/help.html",
		data: { restricted: false }
	});
	
	$routeProvider.when("/credits", {
		templateUrl: "views/credits.html",
		data: { restricted: false }
	});
	
	$routeProvider.when("/privacy", {
		templateUrl: "views/privacy.html",
		data: { restricted: false }
	});

	$routeProvider.when("/evaluation", {
		templateUrl: "views/evaluation.html",
		controller: "evaluationCtrl"
	});
	
	$translateProvider
		.useSanitizeValueStrategy('escape')
		.useStaticFilesLoader({
			prefix: 'i18n/',
			suffix: '.json'
	});
	
	tmhDynamicLocaleProvider.localeLocationPattern('i18n/angular-locale_{{locale}}.js');
	
	var today = new Date();
	var expDate = new Date(today.setFullYear(today.getFullYear() + 1));
		
	$cookiesProvider.defaults.expires = expDate;

	$httpProvider.interceptors.push(['$q', '$location', '$window', '$rootScope', function ($q, $location, $window, $rootScope) {
	return {
		'request': function (config) {
			config.headers = config.headers || {};
			if ($window.localStorage.getItem('token')) {
				config.headers.Authorization = 'Bearer ' + $window.localStorage.getItem('token');
			}
			return config;
		},
		'responseError': function (response) {
			if (response.status === 401 || response.status === 403) {
				$window.localStorage.removeItem('token');
				$rootScope.redirectTo = null;
				$location.path('/login');
			}
			return $q.reject(response);
		}
	};
}]);
	
	
})
.run(function($rootScope, $location, $cookies, $window, $translate, tmhDynamicLocale, ngDialog) {
	
	$rootScope.langKey = $cookies.get('gwap_userLocale') ? $cookies.get('gwap_userLocale') : 'en';	
	$translate.use($rootScope.langKey);
	tmhDynamicLocale.set($rootScope.langKey);
	
	$rootScope.dontShowHelp = false;
	
    $rootScope.$on( "$routeChangeStart", function(event, next, current) {
		if(next.data) {
			var restricted = next.data.restricted;
			if (restricted) {				
				if($window.localStorage.getItem('token') == null) {
					event.preventDefault();
					$rootScope.redirectTo = next.originalPath;
					$location.path("/login");
				} else {
					var allowAnonymous = next.data.allowAnonymous
					 if(!allowAnonymous && $cookies.get('gwap_userSocial') == 'anonymous') {
						event.preventDefault();
						$rootScope.redirectTo = next.originalPath;
						$rootScope.allowAnonymous = allowAnonymous;
						$location.path("/login");
					 }
				}
				
			}
		}		
    });
	
	$rootScope.$on( "$routeChangeSuccess", function(event, current, previous) {

		if(current.originalPath != "/game/:level") {
			if(previous && previous.originalPath == "/game/:level") {
				if(previous.scope.countdownStop){
					previous.scope.stop();
				}
				previous.scope.gameIsStart = 0;
			}			
		}
		if(current.originalPath == "/home") {
			if(!$rootScope.dontShowHelp && ($cookies.get('gwap_dontShowHelp') == undefined || $cookies.get('gwap_dontShowHelp') == "false")) {
				$rootScope.showHowToPlay();
			}
		} 		
    });
		
	$rootScope.goTo = function(path) {
		$location.path(path);
	}
	
	$rootScope.goBack = function() {
		$window.history.back();
	};
	
	$rootScope.toDate = function (date) {
		if(date) {
			var t = date.split(/[- :]/);

			var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
			return d;
		}		
	};
	
	$rootScope.changeLanguage = function (key) {
		$translate.use(key);
		tmhDynamicLocale.set(key);
		$rootScope.langKey = key;
		$cookies.put('gwap_userLocale', key); //
		$rootScope.dontShowHelp = false; //IF LOCALE CHANGES MESSAGE IS SHOWN AGAIN
		$rootScope.showHowToPlay();
	};
	
	$rootScope.showHowToPlay = function () {	
		ngDialog.open({ 
			template: 'views/howToPlayPopUp.html',
			closeByDocument: false,
			showClose: false
		}); 
	};
	
	$rootScope.closeHowToPlay = function (dontShowHelp) {
		$rootScope.dontShowHelp = true;	//TO NOT SHOW THE MESSAGE AGAIN, UNLESS USER RELOAD PAGE (F5)
		$cookies.put('gwap_dontShowHelp', dontShowHelp);
		ngDialog.close();
	};
});