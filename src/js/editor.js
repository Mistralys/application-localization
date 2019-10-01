var Editor = 
{
	'el':null,
	'form':null,
	'textarea':null,
		
	Toggle:function(hash)
	{
		this.DetectElement(hash);
		
		if(this.el.hasClass('active'))
		{
			this.el.removeClass('active');
			this.el.addClass('inactive');
			this.form.hide();
		}
		else
		{
			this.el.addClass('active');
			this.el.removeClass('inactive');
			this.form.show();
			
			this.textarea.focus();
		}
	},
	
	Confirm:function(hash)
	{
		this.DetectElement(hash);
		
		var value = this.textarea.val();
		value = value.trim();
		
		if(value == '') {
			this.textarea.focus();
			return;
		}
		
		// save the trimmed value
		this.textarea.val(value);
		
		this.el.find('TD.string-status').html('<i class="fa fa-check text-success"></i>');
		this.el.find('TD.string-text').html(value);
		this.el.addClass('table-success');
		
		this.Toggle(hash);
	},
	
	DetectElement:function(hash)
	{
		this.el = $("tr[data-hash='"+hash+"']");
		this.form = this.el.next();
		this.textarea = this.form.find('textarea');
	}
};