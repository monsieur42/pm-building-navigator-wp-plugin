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

			var $input = $('input[name="pmbn-appconfigs['+app.id+']"]');

			//SAVE DATA
			app.eventBus.on('save', (config, promises) => {
				const response = () => {
					return new Promise((resolve, reject) => {
						$input.val(JSON.stringify(config));
					});
				}
				promises.push(response());
			});

			//ADD IMAGES
			function initAddImages(){
				var mediaUploader;
				var _mur;
				var openUploader = function(){
					return new Promise((resolve, reject) => {
						_mur = resolve;
						if (mediaUploader) {
							mediaUploader.open();
							return;
						}
						
						var options = {
							frame: 'post',
							title: 'Add Images',
							button: {
								text: 'Add Images',
							},
							library: {
								type: 'image',
							},
							multiple: 'add',
						};

						mediaUploader = new wp.media.view.MediaFrame.Select(options);

						mediaUploader.on('select', function() {
							var attachments = mediaUploader.state().get('selection').toJSON();
							let items = [];
							$.each(attachments, (i, attachment) => {
								items.push({
									id: attachment.id,
									caption: attachment.caption,
									alt: attachment.alt,
									name: attachment.name,
									filename: attachment.filename,
									url: attachment.url,
									sizes: attachment.sizes,
									mime: attachment.mime,
									width: attachment.width,
									height: attachment.height,
								});
							});
							_mur(items);
						});
						mediaUploader.open();
					});
				};
				app.eventBus.on('addImage', (data) => {
					data.promise = openUploader();
				});
			}
			initAddImages();
			
			//ADD FILE
			function initAddFile(){
				var mediaUploader;
				var _mur;
				var openUploader = function(){
					return new Promise((resolve, reject) => {
						_mur = resolve;
						if (mediaUploader) {
							mediaUploader.open();
							return;
						}
						
						var options = {
							frame: 'post',
							title: 'Add File',
							button: {
								text: 'Add File',
							},
							library: {
								type: 'application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-word.document.macroEnabled.12,application/vnd.ms-word.template.macroEnabled.12,application/vnd.oasis.opendocument.text,application/vnd.apple.pages,application/pdf,application/vnd.ms-xpsdocument,application/oxps,application/rtf,application/wordperfect,application/octet-stream',
							},
							multiple: false,
						};

						mediaUploader = new wp.media.view.MediaFrame.Select(options);

						mediaUploader.on('select', function() {
							var attachment = mediaUploader.state().get('selection').first().toJSON();
							_mur(attachment);
						});
						mediaUploader.open();
					});
				};
				app.eventBus.on('addFile', (data) => {
					data.promise = openUploader();
				});
			}
			initAddFile();
		});
	}

	$(window).one('pmbn_init',() => {
		load_pmbn_apps();
	});

	let valid_form = false;
	$('.post-type-building-navigators form[name="post"]').submit(function(e){

		if(valid_form){
			return;
		}
		e.preventDefault();
		let $form = $(this);

		let data = new FormData($form[0]);
		data.append('action', 'pmbn_validate_building_config');

		$form.find('#publishing-action .spinner').addClass('is-active');

		$.ajax(PMBNData.URLS.ajax_url, {
			data: data,
			processData: false,
			contentType: false,
			method: 'POST',
			success: function( response, status, xhr ){
				console.log( response);

				if($.isEmptyObject(response.errors)){
					valid_form = true;

					if(e.originalEvent && e.originalEvent.submitter && e.originalEvent.submitter.name){
						$form.append('<input type="hidden" name="'+e.originalEvent.submitter.name+'" value="'+e.originalEvent.submitter.value+'" />');
					}
					$form.submit();
				}

				$form.find('#publishing-action .spinner').removeClass('is-active');
			},
			error: function( xhr, status, error){
				console.log( xhr, status, error);
				$form.find('#publishing-action .spinner').removeClass('is-active');
			},
		});
	});

})(jQuery);