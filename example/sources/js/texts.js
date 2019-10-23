
var text = t('This text is in a javascript file.');

function bothWorlds()
{
	return t('This text is used serverside and on the client.');
}

var multiline = t(
	'It is possible to write long texts ' +
	'using text concatenation in the source ' +
	'code to keep it readable.'
);

var someVariable = '';

var text2 = t('Texts with variables in them are ignored.'+someVariable);