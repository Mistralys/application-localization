
var text = t('Global context');

var text = t("With double quotes");

function bothWorlds()
{
	return t('Within function');
}

var multiline = t(
	'Multiline ' +
	'text '+
	'over '+
	'several '+
	'concatenated ' +
	'lines'
);

var someVariable = '';

var withVar = t('With variable'+someVariable);

var withClosure = t(function() {
	return t('Within a closure');
});

function JS_Class()
{
	this.translateMe = function()
	{
		return t('Within class method.');
	}
}

var withPlaceholders = t('This is %1$sbold%2$s text.', '<b>', '</b>');

var inception = t('A %1$s text within a translated text.', t('translated text'));