function showToast(text = null, heading = null, type = null) {

	type = (type) ? type.toLowerCase().trim() : null;
	let bgColor, textColor;

	switch(type) {
		case 'success': bgColor = '#0070ba'; textColor = '#fff'; break;
		case 'warning': bgColor = '#ffb540'; textColor = '#000'; break;
		case 'error': bgColor = '#ac0000'; textColor = '#fff'; break;
		default: bgColor = '#0070ba'; textColor = '#fff'; break;
	}

	// if(type == 'success') { bgColor = '#0070ba'; textColor = '#fff'; }
	// else if(type == 'warning') { bgColor = '#ffb540'; textColor = '#000'; }
	// else if(type == 'error') { bgColor = '#ac0000'; textColor = '#fff'; }
	// else { bgColor = '#0070ba'; textColor = '#fff'; }

	$.toast({
		text: `<b style="font-size: 14px; font-weight: 600;">${text}</b>`, // Text that is to be shown in the toast
		heading: (heading) ? `<b>${heading}</b>` : '', // Optional heading to be shown on the toast
		icon: false,	// info, warning, error or success
		showHideTransition: 'fade', // fade, slide or plain
		allowToastClose: true, // Boolean value true or false
		hideAfter: 3000, // false to make it sticky or number representing the miliseconds as time after which toast needs to be hidden
		stack: 4, // false if there should be only one toast at a time or a number representing the maximum number of toasts to be shown at a time
		// position: 'top-center', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
		position: {
			top: 80,
			// bottom: '-',
			// left: '-',
			right: 30,
		},
		bgColor: bgColor,  // Background color of the toast
		textColor: textColor,  // Text color of the toast
		textAlign: 'center',  // Text alignment i.e. left, right or center
		loader: false,  // Whether to show loader or not. True by default
		loaderBg: '#fff',  // Background color of the toast loader
		// beforeShow: function () {}, // will be triggered before the toast is shown
		// afterShown: function () {}, // will be triggered after the toat has been shown
		// beforeHide: function () {}, // will be triggered before the toast gets hidden
		// afterHidden: function () {},
	});
}

function isJson(str) {
	try {
		JSON.parse(str);
	} catch(e) {
		return false;
	}
	return true;
}

$('div.jq-toast-single').on('click', function() {
	$(this).hide();
});

// Traduccion para los datatables
let dt_idioma = {
	info: 'Pag. _PAGE_ de _PAGES_',
	infoEmpty: 'No hay datos',
	infoFiltered: '(de los _MAX_ renglones)',
	lengthMenu: 'Mostrar _MENU_ renglones por pagina',
	loadingRecords: 'Cargando datos...',
	processing: 'Procesando...',
	search: 'Buscar:',
	zeroRecords: 'No se encontro nada',
	paginate: {
		first: '<i class="fas fa-angle-double-left"></i>',
		last: '<i class="fas fa-angle-double-right"></i>',
		next: '<i class="fas fa-angle-right"></i>',
		previous: '<i class="fas fa-angle-left"></i>'
	}
};

function getUrlParam(param) {
	let pageURL = window.location.search.substring(1);
	let URLVariables = pageURL.split('&');

	for(let i = 0; i < URLVariables.length; i++) {
		let paramName = URLVariables[i].split('=');

		if(paramName[0] === param) {
			return paramName[1] === undefined ? true : decodeURIComponent(paramName[1]);
		}
	}
}

function uniqid(prefix, more_entropy) {
	// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +    revised by: Kankrelune (http://www.webfaktory.info/)
	// %        note 1: Uses an internal counter (in php_js global) to avoid collision
	// *     example 1: uniqid();
	// *     returns 1: 'a30285b160c14'
	// *     example 2: uniqid('foo');
	// *     returns 2: 'fooa30285b1cd361'
	// *     example 3: uniqid('bar', true);
	// *     returns 3: 'bara20285b23dfd1.31879087'
	if(typeof prefix === 'undefined') prefix = '';

	let retId;
	let formatSeed = function(seed, reqWidth) {
		// to hex str
		seed = parseInt(seed, 10).toString(16);
		// so long we split
		if(reqWidth < seed.length) return seed.slice(seed.length - reqWidth);
		// so short we pad
		if(reqWidth > seed.length) return Array(1 + (reqWidth - seed.length)).join('0') + seed;
		return seed;
	};

	// BEGIN REDUNDANT
	if(!this.php_js) this.php_js = {};
	// END REDUNDANT

	// init seed with big random int
	if(!this.php_js.uniqidSeed) this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
	this.php_js.uniqidSeed++;

	retId = prefix; // start with prefix, add current milliseconds hex string
	retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
	retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
	if(more_entropy) {
		// for more entropy we add a float lower to 10
		retId += (Math.random() * 10).toFixed(8).toString();
	}

	return retId;
}