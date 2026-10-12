( function () {
	const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
	const { getSetting } = window.wc.wcSettings;
	const { createElement } = window.wp.element;
	const { decodeEntities } = window.wp.htmlEntities;

	const settings = getSetting( 'learning_pay_data', {} );
	const label = decodeEntities( settings.title || 'Learning Pay' );
	const description = decodeEntities( settings.description || '' );

	const Content = () => createElement( 'div', null, description );

	registerPaymentMethod( {
		name: 'learning_pay',
		label: createElement( 'span', null, label ),
		content: createElement( Content ),
		edit: createElement( Content ),
		canMakePayment: () => true,
		ariaLabel: label,
		supports: {
			features: settings.supports || [ 'products' ],
		},
	} );
} )();