const path = require('path');

module.exports = {
	entry: path.resolve(__dirname, "app"),

	output: {
		path: path.resolve(__dirname, "app/packages"),

		filename: "admin.min.js",

		library: 'this',

		libraryTarget: [ 'patchwork', '[name]' ]
	}
};