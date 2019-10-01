var translator =
{
	'cutLength':50,
	'stringInfo':{},

	addStringInfo:function(hash, nativeText, translatedText, files)
	{
		var stringInfo = {
			'hash':hash,
			'nativeText':nativeText,
			'translatedText':translatedText,
			'files':files,
			'dialog':null,

			showDialog:function()
			{
				if(this.dialog==null) {
					this.renderDialog();
				}

                this.dialog.modal('show');
			},

			renderDialog:function()
			{
				var body =
				'<p>'+t('Native text:')+'</p>'+
				'<p>'+htmlspecialchars(this.nativeText)+'</p>'+
				'<p>'+t('Translation:')+'</p>'+
				'<p><textarea id="'+this.hash+'_value" style="width:90%" rows="4">'+this.translatedText+'</textarea></p>'+
				'<p>'+t('Found in:')+'</p>'+
				'<p>'+this.files+'</p>';

				var footer =
				DialogHelper.renderButton_close(t('Cancel'))+
				DialogHelper.renderButton_primary(t('Ok'), "translator.getInfo('"+this.hash+"').handle_confirmEdit()");

				this.dialog = DialogHelper.createDialog(
					t('Translation details'),
					body,
					footer
				);

				var info = this;
				this.dialog.on('shown', function() {
					$('#'+info.hash+'_value').focus();
				});
			},

			handle_confirmEdit:function()
			{
				this.translatedText = $('#'+this.hash+'_value').val();
				$('#'+this.hash+'_storage').val(this.translatedText);
				this.dialog.modal('hide');

				this.checkTranslation();
			},

			checkTranslation:function()
			{
				if(this.translatedText.length >= 1) {
					$('#'+this.hash+'_status').html(UI.Icon().OK().MakeSuccess().Render());
					$('#'+this.hash+'_display').html(this.trimText(this.translatedText));
				} else {
					$('#'+this.hash+'_status').html(UI.Icon().NotAvailable().MakeDangerous().Render());
					$('#'+this.hash+'_display').html(this.trimText(this.nativeText));
				}
			},

			trimText:function(text)
			{
				text = strip_tags(text);
				if(text.length > translator.cutLength) {
					text = text.substring(0, translator.cutLength)+' [...]';
				}

				return text;
			}

		};

		this.stringInfo[hash] = stringInfo;
	},

	getInfo:function(hash)
	{
		return this.stringInfo[hash];
	},

	showDialog:function(hash)
	{
		this.stringInfo[hash].showDialog();
	}
};