const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/.eslintrc' );

module.exports = {
	...defaultConfig,
	overrides: [
		{
			files: [ '**/*.test.js' ],
			rules: {
				// React and its renderer are supplied by the wp-scripts Jest adapter.
				'import/no-extraneous-dependencies': [
					'error',
					{
						packageDir: [
							__dirname,
							path.dirname(
								require.resolve(
									'@wojtekmaj/enzyme-adapter-react-17/package.json'
								)
							),
						],
					},
				],
			},
		},
	],
};
