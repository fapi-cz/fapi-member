var path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const plugins = [
    new CopyWebpackPlugin({
        patterns: [
            { from: 'node_modules/sweetalert2/dist/sweetalert2.min.css', to: 'sweetalert2.min.css' },
            { from: 'node_modules/sweetalert2/dist/sweetalert2.js', to: 'sweetalert2.js' },
            { from: 'node_modules/promise-polyfill/dist/polyfill.min.js', to: 'polyfill.min.js' },
            { from: 'node_modules/clipboard/dist/clipboard.min.js', to: 'clipboard.min.js' },
        ]
    })
];

module.exports = [
    {
        output: {
            filename: 'fapi.dist.js',
            path: path.resolve(__dirname, 'media/dist'),
        },
        entry: './media/fapi.js',
        mode: 'production',
        target: 'browserslist',
        plugins: plugins
    },
    {
        output: {
            filename: 'fapi.dev.js',
            path: path.resolve(__dirname, 'media/dist'),
        },
        entry: './media/fapi.js',
        mode: 'development',
        plugins: plugins
    },

];
