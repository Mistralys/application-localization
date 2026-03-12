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

		// Placeholder validation
		var nativeText = this.el.data('native-text') || '';
		var sourceNums = this.PlaceholderValidator.getNumbers(nativeText);

		if(sourceNums.length > 0) {
			var translationNums = this.PlaceholderValidator.getNumbers(value);

			if(!this.PlaceholderValidator.equal(sourceNums, translationNums)) {
				var warningEl = this.form.find('.placeholder-warning');
				if(warningEl.length === 0) {
					warningEl = $('<div class="alert alert-danger placeholder-warning"></div>');
					this.textarea.after(warningEl);
				}
				warningEl.html(
					'<i class="fa fa-exclamation-triangle"></i> ' +
					'<strong>Placeholder mismatch:</strong> ' +
					'Source uses ' + (sourceNums.length > 0 ? sourceNums.map(function(n){ return '%'+n+'$\u2026'; }).join(', ') : 'none') +
					', translation uses ' + (translationNums.length > 0 ? translationNums.map(function(n){ return '%'+n+'$\u2026'; }).join(', ') : 'none') +
					'. Please correct the translation to match the source placeholders.'
				);
				this.textarea.focus();
				return;
			} else {
				// Counts match — remove any stale warning
				this.form.find('.placeholder-warning').remove();
			}
		}

		// save the trimmed value
		this.textarea.val(value);
		
		this.el.find('TD.string-status').html('<i class="fa fa-check text-success"></i>');
		this.el.find('TD.string-text').html(value);
		this.el.addClass('table-success');
		
		this.Toggle(hash);
	},

	PlaceholderValidator: {
		/**
		 * Extracts numbered argument indices from a sprintf format string.
		 * Matches patterns like %1$s, %2$02d, %3$-10.5f …
		 * Returns a sorted array of unique integer argument numbers.
		 * @param {string} text
		 * @returns {number[]}
		 */
		getNumbers: function(text) {
			var matches = text.match(/%([0-9]+)\$/g) || [];
			var nums = matches.map(function(m) {
				return parseInt(m.replace('%', '').replace('$', ''), 10);
			});
			nums = nums.filter(function(v, i, a) { return a.indexOf(v) === i; });
			nums.sort(function(a, b) { return a - b; });
			return nums;
		},

		/**
		 * Returns true if the arrays contain the same integers in the same order.
		 * @param {number[]} a
		 * @param {number[]} b
		 * @returns {boolean}
		 */
		equal: function(a, b) {
			if(a.length !== b.length) { return false; }
			for(var i = 0; i < a.length; i++) {
				if(a[i] !== b[i]) { return false; }
			}
			return true;
		}
	},
	
	DetectElement:function(hash)
	{
		this.el = $("tr[data-hash='"+hash+"']");
		this.form = this.el.next();
		this.textarea = this.form.find('textarea');
	},
	
	Start:function()
	{
		$('[data-toggle="tooltip"]').tooltip({
			'delay':500,
			'container':'body'
		});
	}
};

$('document').ready(function() 
{
	Editor.Start();
});