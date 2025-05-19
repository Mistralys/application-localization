"use strict";

const text1 = t('Global context');

const text2 = t("With double quotes");

function bothWorlds()
{
	return t('Within function');
}

const multiline = t(
	'Multiline ' +
	'text '+
	'over '+
	'several '+
	'concatenated ' +
	'lines'
);

const someVariable = '';

const withVar = t('With variable'+someVariable);

const withClosure = t(function() {
	return t('Within a closure');
});

const OldJSClass = function()
{
	this.translateMe = function()
	{
		return t('Within old class method.');
	}
}

class NewJSClass
{
	translateMe()
	{
		return t('Within new class method.');
	}
}

const withPlaceholders = t('This is %1$sbold%2$s text.', '<b>', '</b>');

const inception = t('A %1$s text within a translated text.', t('translated text'));
