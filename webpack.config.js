var path = require('path');

module.exports = [
    {
        output: {
            filename: 'fapi.dist.js',
            path: path.resolve(__dirname, 'media/dist'),
        },
        entry: './media/fapi.js',
        mode: 'production',
        target: 'browserslist',
    },
    {
        output: {
            filename: 'fapi.dev.js',
            path: path.resolve(__dirname, 'media/dist'),
        },
        entry: './media/fapi.js',
        mode: 'development',
    },
];