const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		'block-extender': './includes/block-editor/blocks/block-extender',
		'fapi-form-block': './includes/block-editor/blocks/fapi-form-block',
	},
};
