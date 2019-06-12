function number_format(x){
	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function replaceAll(string, search, replacement){
    return string.replace(new RegExp(search, 'g'), replacement);
}

function numbers_only(elm){
	elm.value = elm.value.replace(/[^0-9]/g, '');
	return false;
}

function input_only(elm){
	elm.value = elm.value.replace(/[^0-9.+]/g, '');
	return false;
}

function confirmExit(){
	return "You have attempted to leave this page. Are you sure?";
}