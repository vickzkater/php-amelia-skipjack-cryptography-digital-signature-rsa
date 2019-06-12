/* convert display time depends on client locale time format 
 * (c) 2017 Vicky Budiman @vickzkater
 * Updated 27 Mar 2018 (update `convertTimeToLocal` & `convertDateToLocal` using format)
 */

/* convert datetime format */
function convertTstamptoLocal(tstamp) {
	var a = new Date(tstamp * 1000);
	var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
	var year = a.getFullYear();
	var month = months[a.getMonth()];
	var days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
	var day = days[a.getDay()];
	var date = a.getDate();
	var hour = a.getHours();
	if(hour < 10)
		hour = "0"+hour;
	var min = a.getMinutes();
	if(min < 10)
		min = "0"+min;
	var sec = a.getSeconds();
	if(sec < 10)
		sec = "0"+sec;
	var offset =  a.getTimezoneOffset();
	var nom = offset/60;
	var gmt = "GMT";
	if(nom < 0){
		gmt += "+"+Math.abs(nom);
	}else if((nom > 0)){
		gmt += "-"+Math.abs(nom);
	}
	
	var time = day + ', ' + date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec + ' (' + gmt + ')';
	return time;
}

/* get Client's Timezone e.g. GMT+7 */
function getTimezone() {
	var a = new Date();
	var offset =  a.getTimezoneOffset();
	var nom = offset/60;
	
	return nom;
}

/* convert time format only */
function convertTimeToLocal(tstamp, mode24=false, seconds=false) {
	if(tstamp){
		var a = new Date(tstamp * 1000);
	}else{
		var a = new Date();
	}
	
	var hour = a.getHours();
	if(hour < 10)
		hour = "0"+hour;
	var min = a.getMinutes();
	if(min < 10)
		min = "0"+min;
	var sec = a.getSeconds();
	if(sec < 10)
		sec = "0"+sec;
	
	var time = "";
	if(mode24){
		time += hour + ':' + min;
		if(seconds){
			time += ':' + sec;
		}
	}else{
		var ampm = "";
		if(hour > 12){
			hour = hour - 12;
			if(hour < 10)
				hour = "0"+hour;
			ampm = "PM";
		}else{
			ampm = "AM";
		}
		
		time += hour + ':' + min;
		if(seconds){
			time += ':' + sec;
		}
		time += ' ' + ampm;
	}
	
	return time;
}

/* convert date format only */
function convertDateToLocal(tstamp, format=null, separator=null){
  if(tstamp){
	var a = new Date(tstamp * 1000);
  }else{
	var a = new Date();
  }
  
  var year = a.getFullYear();
  var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  var months2 = ['January','February','March','April','May','June','July','August','September','October','November','December'];
	var mon = a.getMonth();
  var mon2 = mon;
  if(mon2 < 10){
    mon2 = "0"+mon;
  }
	var date = a.getDate();
  var date2 = date;
  if(date2 < 10){
    date2 = "0"+date;
  }
  
  var time = "";
  if(format && separator){
    var arrForm = format.split(separator);
    /*
		d = 1-31
		dd = 01-31
		m = 1-12
		mm = 01-12
		M = Jan, Feb
		MM = January, February
		yyyy = 1993
    */
    var arrRes = [];
    var i = 0;
    
    switch(arrForm[i]){
      case 'd':
        arrRes.push(date);
        break;
      case 'dd':
        arrRes.push(date2);
        break;
      case 'm':
        arrRes.push(mon);
        break;
      case 'mm':
        arrRes.push(mon2);
        break;
      case 'M':
        arrRes.push(months[mon]);
        break;
      case 'MM':
        arrRes.push(months2[mon]);
        break;
      case 'yyyy':
        arrRes.push(year);
        break;
    }
    i++;
    switch(arrForm[i]){
      case 'd':
        arrRes.push(date);
        break;
      case 'dd':
        arrRes.push(date2);
        break;
      case 'm':
        arrRes.push(mon);
        break;
      case 'mm':
        arrRes.push(mon2);
        break;
      case 'M':
        arrRes.push(months[mon]);
        break;
      case 'MM':
        arrRes.push(months2[mon]);
        break;
      case 'yyyy':
        arrRes.push(year);
        break;
    }
    i++;
    switch(arrForm[i]){
      case 'd':
        arrRes.push(date);
        break;
      case 'dd':
        arrRes.push(date2);
        break;
      case 'm':
        arrRes.push(mon);
        break;
      case 'mm':
        arrRes.push(mon2);
        break;
      case 'M':
        arrRes.push(months[mon]);
        break;
      case 'MM':
        arrRes.push(months2[mon]);
        break;
      case 'yyyy':
        arrRes.push(year);
        break;
    }
  
    var result = arrRes.join(separator);
    return result;
  }
}

/* for execute datetime format converter */
function convertTimeThis() {
	/* for convert from element to display the result in html value */
	var dtfield = document.getElementsByClassName("vdate");
	var total = dtfield.length;
	var event = $(".vdate").map(function(){return $(this).attr("event");}).get();

	for (var i = 0; i < total; i++) {
		dtfield[i].innerHTML = convertTstamptoLocal(event[i]);
	}
	
	/* for convert from element to display the result in title value */
	var dtfield2 = document.getElementsByClassName("titledate");
	var total2 = dtfield2.length;
	var titledate = $(".titledate").map(function(){return $(this).attr("event");}).get();

	for (var i2 = 0; i2 < total2; i2++) {
		dtfield2[i2].setAttribute("title", convertTstamptoLocal(titledate[i2]));
	}
	
	/* for convert from element to display the result in data-original-title value (bootstrap) */
	var dtfield3 = document.getElementsByClassName("icondate");
	var total3 = dtfield3.length;
	var icontitle = $(".icondate").map(function(){return $(this).attr("event");}).get();

	for (var i3 = 0; i3 < total3; i3++) {
		dtfield3[i3].setAttribute("data-original-title", convertTstamptoLocal(icontitle[i3]));
		dtfield3[i3].setAttribute("title", convertTstamptoLocal(icontitle[i3]));
	}
	
	/* for convert from element to display the result in input value */
	var dtfield4 = document.getElementsByClassName("inputdate");
	var total4 = dtfield4.length;
	var inputdate = $(".inputdate").map(function(){return $(this).attr("event");}).get();

	for (var i4 = 0; i4 < total4; i4++) {
		dtfield4[i4].value = convertTstamptoLocal(inputdate[i4]);
	}
}
