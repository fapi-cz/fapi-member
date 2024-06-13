const path = require('path');

module.exports = {
  mode: process.env.NODE_ENV || 'development',
  entry: './src/index.js',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'bundle.js',
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
      'Components': path.resolve(__dirname, 'src/Components'),
      'Enums': path.resolve(__dirname, 'src/Enums'),
      'Services': path.resolve(__dirname, 'src/Services'),
      'Models': path.resolve(__dirname, 'src/Models'),
      'Clients': path.resolve(__dirname, 'src/Clients'),
      'Helpers': path.resolve(__dirname, 'src/Helpers'),
      'Hooks': path.resolve(__dirname, 'src/Hooks'),
      'Styles': path.resolve(__dirname, 'src/Styles'),
      'Images': path.resolve(__dirname, 'src/Media/Images'),
    },
    extensions: ['.js', '.jsx'],
  },
  module: {
    rules: [
        {
        test: /\.(png|jpe?g)$/i,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[path][name].[ext]',
            },
          },
        ],
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react'],
          },
        },
      },
      {
        test: /\.css$/,
        use: [
          'style-loader', // Injects styles into DOM
          'css-loader'    // Turns CSS into CommonJS
        ]
      },
      {
        test: /\.scss$/,
        use: [
          'style-loader', // Injects styles into DOM
          'css-loader',   // Turns CSS into CommonJS
          'sass-loader'   // Compiles Sass to CSS
        ]
      },
      {
        test: /\.svg$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[ext]',
              outputPath: '/images',
            },
          },
        ],
      }
    ],
  },
};
