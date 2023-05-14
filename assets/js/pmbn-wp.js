(function($) {
	function load_pmbn_data(){
		if(window.PMBNData){
			if(window.PMBNData.i18n){
				window.pmbn.i18n = window.PMBNData.i18n;
			}
		}
		
	}

	$(window).one('pmbn_before_init',() => {
		load_pmbn_data();
	});

	function load_pmbn_apps(){
		$.each(window.pmbn.apps, function(i, app){
			//LOAD DATA
			if(window.PMBNData){
				var appConfig = PMBNData.buildings.find((bc, i) => bc.id == app.id);
				if(appConfig){
					app.loadConfig(appConfig.config);
				}
			}
		});
	}

	$(window).one('pmbn_init',() => {
		load_pmbn_apps();
	});

})(jQuery);