const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const WPDependencyExtraction = require('@wordpress/dependency-extraction-webpack-plugin');

module.exports = {
	entry: {
		app: './app/src/app.js',
		api: './app/src/api-client/index.js'
	},

	output: {
		path: path.resolve(__dirname, 'app/dist'),

		filename: '[name].js',

		library: [ 'patchwork', '[name]' ],

		libraryTarget: 'this'
	},

	module: {
		rules: [
			{
				test: /\.jsx?$/,
				loader: "babel-loader",
				exclude: /node_modules/,
				options: {
					rootMode: "upward",
				}
			},
			{
				test: /\.*css$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
						options: {
							publicPath: path.resolve(__dirname, "app/dist")
						}
					},
					"css-loader",
					{
						loader: 'sass-loader',
						options: {
							implementation: require('sass')
						}
					}
				]
			}
		]
	},

	plugins: [
		new MiniCssExtractPlugin({
			filename: '[name].css',
			chunkFilename: '[id].css',
			ignoreOrder: false
		}),
		new WPDependencyExtraction()
	]
};