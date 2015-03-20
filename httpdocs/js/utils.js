String.prototype.paddingLeft = function(paddingValue)
{
	return String(paddingValue + this).slice(-paddingValue.length);
};