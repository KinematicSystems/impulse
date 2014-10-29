angular.module('settingsModule', ['services.SettingsService', 'ngTable' ])

.controller('SettingsController', [
		'$scope',
		'$filter',
		'settingsService',
		'ngTableParams',
		function($scope, $filter, settingsService, ngTableParams, $sce) {
			$scope.pageTitle = 'Settings';
			$scope.alerts = [];
			$scope.domainList = [];
			$scope.currentDomain = "";

			settingsService.getDomains().success(function(data, status) {
				$scope.domainList = data;
			});

			$scope.closeAlert = function(index) {
				$scope.alerts.splice(index, 1);
			};

			
			/* jshint ignore:start */
			$scope.tableParams = new  ngTableParams ({
				page : 1, // show first page
				count : 200, // count per page
				sorting : {
					domain : "asc" // initial sorting
				}
			} 	
			, {
				groupBy : function(item) {
					if (item.parent != null)
					{	
						return item.parent + "/" + item.domain;
					}
					else
					{
						return item.domain;
					}
				},
				counts : [], // hide page counts control
				total : 1, // value less than count hide pagination

				getData : function($defer, params) {
					settingsService.getAll().success(
							function(data, status) {

								$scope.settingsList = $filter('orderBy')(data,
										'domain');
								params.total(data.length);
								$defer.resolve($scope.settingsList);
							});
				}
			});
			/* jshint ignore:end */
			
			$scope.editSetting = function(setting, $event) {
				$event.stopPropagation();
				if (!setting.underEdit) {
					setting.underEdit = true;
					setting.previousValue = setting.value;
				}
			};

			$scope.saveSetting = function(setting, $event) {
				$event.stopPropagation();
				// Remove the editing properties before making service call
				// since underEdit and previousValue do not get persisted
				delete setting.underEdit;
				delete setting.previousValue;
				settingsService.update(setting).success(function(data, status) {
					setting.underEdit = false;
				}).error(function(data, status, headers, config) {
					$scope.alerts.push({
						type : 'danger',
						msg : 'Error saving setting'
					});
					setting.underEdit = false;
				});
			};

			$scope.cancelEditSetting = function(setting, $event) {
				$event.stopPropagation();
				setting.value = setting.previousValue;
				setting.underEdit = false;
			};

			/*
			 * settingsService.getAll().success(function(data, status) {
			 * $scope.settingsList = data; var domains = new Array();
			 * 
			 * var prevDomain = null; for (var i=0; i < data.length; ++i) { var
			 * setting = data[i]; if (setting.domain !== prevDomain) {
			 * domains.push(setting.domain); prevDomain = setting.domain; } }
			 * $scope.domainList = domains;
			 * 
			 * });
			 */
		} ]);
